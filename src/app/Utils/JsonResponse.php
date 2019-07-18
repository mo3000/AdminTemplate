<?php


namespace App\Utils;


class JsonResponse implements \JsonSerializable
{
	private $code;
	private $msg;
	private $data;

	public function __construct($code, $msg='', $data=null)
	{
		$this->code = $code;
		$this->msg = $msg;
		$this->data = $data;
	}

	public function jsonSerialize()
	{
		return [
			'code' => $this->code,
			'msg' => $this->msg,
			'data' => $this->data
		];
	}
}