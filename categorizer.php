<?php
/*
W-PHP Categorizer
=====================
File: categorizer.php
Author: Ali Candan [Webkolog] <webkolog@gmail.com> 
Homepage: http://webkolog.net
GitHub Repo: https://github.com/webkolog/php-categorizer
Last Modified: 2016-08-26
Created Date: 2013-10-02
Compatibility: PHP 5.4+
@version     1.1

Copyright (C) 2015 Ali Candan
Licensed under the MIT license http://mit-license.org

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class Categorizer {

	public $tableName = null;
	public $colId = "id";
	public $colTop = "top_id";
	public $colName = "name";
	public $listRow = null;
	public $orderType = null;
	public $selectedId = 0;
	private $categories = array();
	public $treeList = array();
	public $nestedList = array();
	public $filter = null;
	public $extraCols = array();
	public $filterValues = array();
	private $db;
	
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	private function findTopCat($topId)
	{
		foreach($this->treeList as $category)
		{
			if ($category["id"] == $topId)
			{
				$categories = array("id" => $category["id"], "top" => $category["top"], "name" => $category["name"]);
				$i = 3;
				foreach ($this->extraCols as $currentCol)
				{
					$categories[$currentCol] = $category[$currentCol];
					$i++;
				}
				array_push($this->nestedList, $categories);
				$this->findTopCat($category["top"]);
				break;
			}
		}
	}
	
	private function makeNested()
	{
		foreach($this->treeList as $category)
		{
			if ($category["id"] == $this->selectedId)
			{
				$categories = array("id" => $category["id"], "top" => $category["top"], "name" => $category["name"]);
				$i = 3;
				foreach ($this->extraCols as $currentCol)
				{
					$categories[$currentCol] = $category[$currentCol];
					$i++;
				}
				array_push($this->nestedList, $categories);
				$this->findTopCat($category["top"]);
				break;
			}
		}
	}
	
	private function findSubCat($counter, $cat)
	{
		$counter++;
		foreach($this->categories as $category)
		{
			if ($category["top"] == $cat["id"])
			{
				$selected = $category["id"] == $this->selectedId;
				$categories = array("id" => $category["id"], "top" => $category["top"], "name" => $category["name"], "depth" => $counter, "selected" => $selected);
				$i = 3;
				foreach ($this->extraCols as $currentCol)
				{
					$categories[$currentCol] = $category[$currentCol];
					$i++;
				}
				array_push($this->treeList, $categories);
				$this->findSubCat($counter, $category);
			}
		}
	}
	
	private function makeTree()
	{
		if ($this->filter == null)
			$filter = null;
		else
			$filter = " WHERE ".$this->filter;
		$i = 3;
		if (count($this->extraCols))
		{
			$extraCols = array();
			foreach ($this->extraCols as $currentCol)
			{
				array_push($extraCols, $currentCol);
				$i++;
			}
			$extraCols = ", ".join(", ", $extraCols);
		}
		else 
		{
			$extraCols = "";
			$extraColsID = null;
		}
		$sth = $this->db->prepare("SELECT {$this->colId}, {$this->colTop}, {$this->colName}{$extraCols} FROM {$this->tableName}{$filter} ".($this->listRow!=null?"ORDER BY {$this->colTop}, ".$this->listRow." ".$this->orderType:null));
		foreach ($this->filterValues as $key => $value) {
			$new_key = is_numeric($key) ? $key + 1 : $key;
			$sth->bindValue($new_key, $value);
		}
		$sth->execute();
		$sqlQuery = $sth->fetchAll(PDO::FETCH_NUM);
		foreach ($sqlQuery as $rs){
			$categories = array("id" => $rs[0], "top" => $rs[1], "name" => $rs[2]);
			$i = 3;
			foreach ($this->extraCols as $currentCol)
			{
				$categories[$currentCol] = $rs[$i];
				$i++;
			}
			array_push($this->categories, $categories);
		}
		foreach($this->categories as $category)
		{
			if ($category["top"] == 0)
			{
				$selected = ($category["id"]==$this->selectedId?true:false);
				$categories = array("id" => $category["id"], "top" => $category["top"], "name" => $category["name"], "depth" => 0, "selected" => $selected);
				$i = 3;
				foreach ($this->extraCols AS $currentCol)
				{
					$categories[$currentCol] = $category[$currentCol];
					$i++;
				}
				array_push($this->treeList, $categories);
				$this->findSubCat(0, $category);
			}
		}
	}
	
	public function makeCategorize()
	{
		$this->makeTree();
		$this->makeNested();
		$this->nestedList = array_reverse($this->nestedList);
	}
	
}