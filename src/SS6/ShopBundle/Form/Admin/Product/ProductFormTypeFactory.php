<?php

namespace SS6\ShopBundle\Form\Admin\Product;

use SS6\ShopBundle\Model\FileUpload\FileUpload;
use SS6\ShopBundle\Model\Pricing\Vat\VatRepository;
use SS6\ShopBundle\Model\Product\Availability\AvailabilityRepository;

class ProductFormTypeFactory {

	/**
	 * @var \SS6\ShopBundle\Model\FileUpload\FileUpload
	 */
	private $fileUpload;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Vat\VatRepository
	 */
	private $vatRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Availability\AvailabilityRepository
	 */
	private $availabilityRepository;

	/**
	 * @param \SS6\ShopBundle\Model\FileUpload\FileUpload $fileUpload
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\VatRepository $vatRepository
	 * @param \SS6\ShopBundle\Model\Product\Availability\AvailabilityRepository $availabilityRepository
	 */
	public function __construct(
		FileUpload $fileUpload,
		VatRepository $vatRepository,
		AvailabilityRepository $availabilityRepository
	) {
		$this->fileUpload = $fileUpload;
		$this->vatRepository = $vatRepository;
		$this->availabilityRepository = $availabilityRepository;
	}

	/**
	 * @return \SS6\ShopBundle\Form\Admin\Product\ProductFormType
	 */
	public function create() {
		$vats = $this->vatRepository->findAll();
		$availabilities = $this->availabilityRepository->findAll();

		return new ProductFormType($this->fileUpload, $vats, $availabilities);
	}

}