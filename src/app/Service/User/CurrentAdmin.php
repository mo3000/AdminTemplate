<?php

namespace App\Service\User;

use App\Admin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;

class CurrentAdmin {
	private $admin;

	public function getNickname() : string {
		return $this->admin->nickname;
	}

	public function getRealname() : string {
		return $this->admin->realname;
	}

	public function getId() {
		return $this->admin->id;
	}

	public function getName() {
		return $this->admin->name;
	}

	public function getAuthcode() {
		return $this->admin->authcode;
	}

	public function getAdmin()
	{
		return $this->admin;
	}


	public function __construct($id) {
		$admin = Admin::find($id);
		if (empty($admin)) {
			throw new AuthenticationException('user not found: '.$id);
		}
		$this->admin = $admin;
	}
}