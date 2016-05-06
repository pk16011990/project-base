<?php

namespace SS6\ShopBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade;
use SS6\ShopBundle\Model\Product\Pricing\ProductCalculatedPriceRepository;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculation;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductService;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductPriceRecalculator {

	const BATCH_SIZE = 100;
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculation
	 */
	private $productPriceCalculation;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductCalculatedPriceRepository
	 */
	private $productCalculatedPriceRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
	 */
	private $productPriceRecalculationScheduler;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade
	 */
	private $pricingGroupFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroup[]|null
	 */
	private $allPricingGroups;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductService
	 */
	private $productService;

	/**
	 * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\SS6\ShopBundle\Model\Product\Product[][0]|null
	 */
	private $productRowsIterator;

	public function __construct(
		EntityManager $em,
		ProductPriceCalculation $productPriceCalculation,
		ProductCalculatedPriceRepository $productCalculatedPriceRepository,
		ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
		PricingGroupFacade $pricingGroupFacade,
		ProductService $productService
	) {
		$this->em = $em;
		$this->productPriceCalculation = $productPriceCalculation;
		$this->productCalculatedPriceRepository = $productCalculatedPriceRepository;
		$this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
		$this->pricingGroupFacade = $pricingGroupFacade;
		$this->productService = $productService;
	}

	/**
	 * @return bool
	 */
	public function runBatchOfScheduledDelayedRecalculations() {
		if ($this->productRowsIterator === null) {
			$this->productRowsIterator = $this->productPriceRecalculationScheduler->getProductsIteratorForDelayedRecalculation();
		}

		for ($count = 0; $count < self::BATCH_SIZE; $count++) {
			$row = $this->productRowsIterator->next();
			if ($row === false) {
				$this->clearCache();
				$this->em->clear();

				return false;
			}
			$this->recalculateProductPrices($row[0]);
		}
		$this->clearCache();
		$this->em->clear();

		return true;
	}

	public function runAllScheduledRecalculations() {
		$this->runImmediateRecalculations();

		$this->productRowsIterator = null;
		// @codingStandardsIgnoreStart
		while ($this->runBatchOfScheduledDelayedRecalculations()) {};
		// @codingStandardsIgnoreEnd
	}

	public function runImmediateRecalculations() {
		$products = $this->productPriceRecalculationScheduler->getProductsForImmediateRecalculation();
		foreach ($products as $product) {
			$this->recalculateProductPrices($product);
		}
		$this->productPriceRecalculationScheduler->cleanScheduleForImmediateRecalculation();
		$this->clearCache();
	}

	private function clearCache() {
		$this->allPricingGroups = null;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Pricing\Group\PricingGroup[]
	 */
	private function getAllPricingGroups() {
		if ($this->allPricingGroups === null) {
			$this->allPricingGroups = $this->pricingGroupFacade->getAll();
		}

		return $this->allPricingGroups;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 */
	private function recalculateProductPrices(Product $product) {
		foreach ($this->getAllPricingGroups() as $pricingGroup) {
			try {
				$price = $this->productPriceCalculation->calculatePrice($product, $pricingGroup->getDomainId(), $pricingGroup);
				$priceWithVat = $price->getPriceWithVat();
			} catch (\SS6\ShopBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException $e) {
				$priceWithVat = null;
			}
			$this->productCalculatedPriceRepository->saveCalculatedPrice($product, $pricingGroup, $priceWithVat);
		}
		$product->markPriceAsRecalculated();
		$this->productService->markProductForVisibilityRecalculation($product);
		$this->em->flush($product);
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event) {
		if ($event->isMasterRequest()) {
			$this->runImmediateRecalculations();
		}
	}

}
