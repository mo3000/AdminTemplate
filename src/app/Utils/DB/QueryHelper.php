<?php

namespace App\Utils\DB;


use Illuminate\Http\Request;

class QueryHelper {
	private $query;
	private $request;

	public function __construct($query)
	{
		$this->query = $query;
		$this->request = resolve(Request::class);
	}

	public function like(string $fieldname, ?string $requestFieldname='')
	{
		$realFieldname = empty($requestFieldname) ?
			$fieldname : $requestFieldname;
		if ($this->request->filled($realFieldname)) {
			$this->query->where(
				$fieldname,
				'like',
				'%'.$this->request->input($realFieldname).'%'
			);
		}

		return $this;
	}

	public function equal(string $fieldname, ?string $requestFieldname='', ?\Closure $func=null)
	{
		$realFieldname = empty($requestFieldname) ?
			$fieldname : $requestFieldname;
		$value = is_callable($func) ?
			$func($this->request->input($realFieldname))
				: $this->request->input($realFieldname);

		if (!empty($value) && $value != -1) {
			$this->query->where($fieldname, $value);
		}
		return $this;
	}

	public function compare(string $symbol, string $fieldname, ?string $requestFieldname='', ?\Closure $func=null)
	{
		$realFieldname = empty($requestFieldname) ?
			$fieldname : $requestFieldname;
		$value = is_callable($func) ?
			$func($this->request->input($realFieldname))
			: $this->request->input($realFieldname);

		if (!empty($value) && $value != -1) {
			$this->query->where($fieldname, $symbol, $value);
		}
		return $this;
	}

	public function in(string $fieldname, ?array $values)
	{
		if (is_array($values) && count($values) > 0) {
			$this->query->whereIn($fieldname, $values);
		}
	}

	public function endHelper()
	{
		return $this->query;
	}
}