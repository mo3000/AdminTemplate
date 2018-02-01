<?php

namespace App\Service\User;

use App\Admin;
use Illuminate\Auth\AuthenticationException;

class CurrentUser {
	private $id;
	private $name;
	private $nickname;
	private $realname;

	public function getNickname() : string {
		return $this->nickname;
	}

	public function getRealname() : string {
		return $this->realname;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function __construct($id) {
		$admin = Admin::find($id);
		if (empty($admin)) {
			throw new AuthenticationException('user not found: '.$id);
		}
		$this->id = $id;
		$this->name = $admin->name;
		$this->nickname = $admin->nickname;
		$this->realname = $admin->realname;

	}

}