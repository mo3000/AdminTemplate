<?php

namespace App\Utils\Format;


class JsonResponse implements \JsonSerializable {
	private $code;
	private $msg;
	private $data;

	public function __construct(int $code, string $msg='', $data=null) {
		$this->code = $code;
		$this->msg = $msg;
		$this->data = $data;
	}

	public function jsonSerialize() {
		return [
				'code' => $this->code,
				'msg' => $this->msg,
				'data' => $this->data
			];
	}

}