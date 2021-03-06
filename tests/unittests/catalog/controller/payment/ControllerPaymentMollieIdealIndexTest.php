<?php

class ControllerPaymentMollieIdealIndexTest extends Mollie_OpenCart_TestCase
{
	/**
	 * @var ControllerPaymentMollieIdeal|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $controller;

	/**
	 * @var iDEAL_Payment|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $ideal;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $config;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $language;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $url;

	public function setUp()
	{
		$this->controller = $this->getMock("ControllerPaymentMollieIdeal", array("getIdealPaymentObject", "render"));
		$this->controller->request = new stdClass();
		$this->controller->request->get = array();
		$this->controller->request->post = array();
		$this->controller->request->server = array();

		$this->controller->cart = $this->getMock("stub", array("clear"));

		$this->ideal = $this->getMock("iDEAL_Payment", array("setPartnerId", "setProfileKey", "setTestmode", "getBanks", "checkPayment", "getBankStatus", "getAmount"), array(self::CONFIG_PARTNER_ID));

		$this->controller->expects($this->any())
			->method("getIdealPaymentObject")
			->with(self::CONFIG_PARTNER_ID)
			->will($this->returnValue($this->ideal));

		$this->config = $this->getMock("stdClass", array("get"));
		$this->config->expects($this->any())
			->method("get")
			->will($this->returnValueMap(array(
			array("mollie_ideal_partnerid", self::CONFIG_PARTNER_ID),
			array("mollie_ideal_profilekey", self::CONFIG_PROFILE_KEY),
			array("mollie_ideal_testmode", self::CONFIG_TESTMODE),
			array("mollie_ideal_processed_status_id", self::ORDER_STATUS_SUCCESS_ID),
			array("mollie_ideal_processing_status_id", self::ORDER_STATUS_PROCESSING_ID),
			array("mollie_ideal_failed_status_id", self::ORDER_STATUS_FAILED_ID),
			array("mollie_ideal_canceled_status_id", self::ORDER_STATUS_CANCELED_ID),
			array("mollie_ideal_expired_status_id", self::ORDER_STATUS_EXPIRED_ID),
		)));

		$this->controller->config = $this->config;

		$this->language = $this->getMock("stub", array("get"));
		$this->language->expects($this->any())
			->method("get")
			->will($this->returnArgument(0));
		$this->controller->language = $this->language;

		$this->url = $this->getMock("stub", array("link"));
		$this->controller->url = $this->url;

		$this->controller->load = $this->getMock("stdClass", array("model", "language"));
	}

	public function testIndex()
	{
		$this->ideal->expects($this->any())
			->method("setPartnerId")
			->with(self::CONFIG_PARTNER_ID);

		$this->ideal->expects($this->once())
			->method("setProfileKey")
			->with(self::CONFIG_PROFILE_KEY);

		$this->ideal->expects($this->once())
			->method("setTestmode")
			->with(self::CONFIG_TESTMODE);

		$this->ideal->expects($this->once())
			->method("getBanks")
			->will($this->returnValue(self::$banks));

		$this->controller->expects($this->once())
			->method("render");

		$this->url->expects($this->once())
			->method("link")
			->with("payment/mollie_ideal/payment", "", "SSL")
			->will($this->returnValue(self::URL_PAYMENT));

		$this->controller->index();

		$this->assertEquals(array(
			"banks"   => self::$banks,
			"action"  => self::URL_PAYMENT,
			"message" => $this->language,
		), $this->controller->data);

		$this->assertEquals("default/template/payment/mollie_ideal_banks.tpl", $this->controller->template);
	}
}