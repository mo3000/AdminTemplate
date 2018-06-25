<?php

namespace App\Service;


use App\Admin;
use App\Permissions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MenuService {
	private $project_name;
	private $gymid;
	private $conf = [];
	private $path_level = 0;
	private $path_index = 0;
	private $dict;
	private $gyms;
	private $uselessKey = 0;

	public function getAll()
	{
		$this->parseConfigFlat();

		return $this->format();
	}

	public function getUserMenu($gymid)
	{
		$this->getDict();

		if (Admin::currentAdmin()->getAdmin()->hasRole('superadmin')) {
			$permissions = $this->getMenuData(
				$gymid
			);
			$permissions2 = $this->getMenuData();
		} else {
			$permissions = $this->getUserMenuData(
				Admin::currentAdmin()->getId(),
				$gymid
			);
            $permissions2 = $this->getUserMenuData(Admin::currentAdmin()->getId());
		}

		$menus = [];

		foreach ($permissions->toArray() as $row) {

			if (!isset($menus[$row['project_name']])) {
				$menus[$row['project_name']] = [];
			}

			$menus[$row['project_name']] = Arr::add(
				$menus[$row['project_name']],
				$row['path_name'],
				1
			);

		}
        foreach ($permissions2->toArray() as $row) {
            $menus[$row['project_name']] = 1;
        }

		return $menus;
	}


	private function toPermissionTree($assocMenus)
	{
		$indexMenus = [];

		foreach ($assocMenus as $k => $v) {

			if (array_key_exists('children', $v)) {
				//叶子节点
				$indexMenus[] = $v;
			} else {
				$indexMenus[] = [
					'children' => $this->toPermissionTree($v),
					'name' => $k,
					'display_name' => $this->translateWord($k),
					'checked' => 0,
					'id' => 'uselessKey'.$this->uselessKey,
				];
				$this->uselessKey++;
			}

		}

		return $indexMenus;
	}



	private function getUserMenuData($userid, $gymid = null)
	{
		return Permissions::where('gymid', $gymid)
		                  ->whereRaw("id in (
			select rp.permissionid 
			from admin_role as ar
			left join role_permission as rp
				on ar.roleid = rp.roleid
			where adminid=?)",
		                             [$userid]
		                  )
		                  ->orderBy('project_name')
		                  ->orderBy('path_name')
		                  ->orderBy('name')
		                  ->get();
	}

	private function getMenuData($gymid = null)
	{
		return Permissions::where('gymid', $gymid)
		                  ->orderBy('project_name')
		                  ->orderBy('path_name')
		                  ->orderBy('name')
		                  ->get();
	}




	private function getDict()
	{
		$this->dict = array_merge(
			config('rbac.dict'),
			config('rbac.project')
		);
		$this->gyms = DB::table('gym')
			->select('id', 'name')
			->get()
			->keyBy('id')
			->toArray();
	}

	private function translateWord($word)
	{

		return isset($this->dict[$word]) ?
				$this->dict[$word] : $word;
	}

	private function translateGym($gymid)
	{
		return $this->gyms[$gymid]['name'];
	}

	//用户权限树
	public function getRolePermissionTree($roleid, $gymid)
	{
		$perms = DB::table('permissions as p')
			->select('p.*')
			->when(!empty($roleid), function ($query) use ($roleid) {
				return $query->selectRaw("p.id in (
					select permissionid from role_permission
					where roleid=?) as checked", [$roleid]);
			})
			->when(empty($roleid), function ($query) {
				return $query->selectRaw("false as checked");
			})
			->where(function ($query) use ($gymid) {
				return $query->where('p.gymid', $gymid)
					->orWhereNull('p.gymid');
			})
			->get();

		$menus = [];

		foreach ($perms->toArray() as $row) {

			if (!isset($menus[$row->project_name])) {
				$menus[$row->project_name] = [];
			}

			$menus[$row->project_name] = Arr::add(
				$menus[$row->project_name],
				$row->path_name.'.'.$row->name,
				[
					'display_name' => $row->display_name,
					'id' => $row->id,
					'children' => null,
					'checked' => $row->checked,
					'name' => $row->name,
				]
			);

		}

		$this->getDict();
		$indexArray = $this->toPermissionTree($menus);
		return $indexArray;
	}

	private function parseConfigFlat()
	{
		foreach (config('rbac.menus') as $gymid => $project) {
			if (intval($gymid) == 0) {
				$this->parse('outer', [$gymid => $project]);
			} else {
				$this->parse($gymid, $project);
			}
		}
	}

	private function format()
	{
		$conf = [];
		foreach ($this->conf as $gymid => $project) {
			foreach ($project as $project_name => $menu) {
				foreach ($menu as $v) {
					$conf[] = [
						'gymid' => $gymid,
						'project_name' => $project_name,
						'path_name' => implode('.', $v['path_name']),
						'name' => $v['name'],
						'display_name' => $v['display_name']
					];
				}
			}
		}
		return $conf;
	}

	//把project分解拼接gymid形成四元组(gymid,project,path_name,name)的数组
	private function parse($gymid, $project)
	{
		if (!isset($this->conf[$gymid])) {
			$this->conf[$gymid] = [];
		}
		$this->gymid = $gymid;
		foreach ($project as $k => $v) {
			$this->project_name = $k;
			if (!isset($this->conf[$this->gymid][$this->project_name])) {
				$this->conf[$this->gymid][$this->project_name] = [];
			}
			$this->path_index = 0;
			$this->parseSubRoute($v);
		}
	}

	private function parseSubRoute($menu)
	{
		foreach ($menu as $k => $v) {
			if (!isset($this->conf[$this->gymid][$this->project_name][$this->path_index])) {

				$this->conf[$this->gymid][$this->project_name][$this->path_index] = ['path_name' => [], 'name' => ''];

				if ($this->path_index > 0) {
					$this->conf[$this->gymid][$this->project_name][$this->path_index]['path_name']
						= array_slice(
						$this->conf[$this->gymid][$this->project_name][$this->path_index - 1]['path_name'],
						0,
						$this->path_level
					);
				}

			}

			if (is_array($v)) {
				$this->conf[$this->gymid][$this->project_name][$this->path_index]['path_name'][] = $k;
				$this->path_level++;
				$this->parseSubRoute($v);
				$this->path_level--;
			} else {
				$this->conf[$this->gymid][$this->project_name][$this->path_index]['name'] = $k;
				$this->conf[$this->gymid][$this->project_name][$this->path_index]['display_name'] = $v;
				$this->path_index++;
			}
		}
	}
}