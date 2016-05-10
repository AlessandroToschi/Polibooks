<?php

class University 
{
	private $id;
	public function __construct($id)
	{
		$this->id = $id;
	}

	public function getCampusList()
	{
		global $db;
		
		$q = $db->query("SELECT * FROM campus WHERE id_uni=".(int)$this->id);

		$arr = array();
		while($r = $db->fetch($q))
		{
			if(strtolower($r['name']) == strtolower($r['city']))
				$name = $r['name'];
			else
				$name = $r['name']." (".$r['city'].")";
			$arr[$r['id']] = $name;
		}
		return $arr;
	}
	
	public function getCampusIds()
	{
		global $db;
		
		$q = $db->query("SELECT id FROM campus WHERE id_uni=".(int)$this->id);

		$arr = array();
		while($r = $db->fetch($q))
			$arr[]=$r['id'];
		return $arr;
	}
}

?>
