<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

	class SqlResultIterator implements Iterator{
		protected $result;
		protected PearDatabase $adb;
		protected int $pos;

		function __construct($adb, $result){
			$this->result = $result;
			$this->adb = $adb;
			$this->pos = 0;
		}
		
		public function rewind() : void {
			$this->pos = 0;
		}
		
		function valid() : bool {
			$adb = $this->adb;
			return $this->pos < $adb->num_rows($this->result);
		}
		
		public function next() : void{
			$this->pos+=1;
		}
		
		public function current(): mixed{
			$adb = $this->adb;
			$data = $adb->raw_query_result_rowdata($this->result, $this->pos);
			return new SqlResultIteratorRow($data);
		}
		
		public function key() : mixed{
			return $this->pos;
		}
		
		
		/**
		 * Return the contents of the resultset as an array. Destroys a running iterator's state.
		 *
		 * This method will reset the iterator using the rewind method.
		 * 
		 * $copyFields specify which fields of the result to copy to the array. 
		 * If not specified the function will return values for all the fields.
		 */
		function toArray($copyFields=null){
			$adb = $this->adb;
			$this->rewind();
			
			if($copyFields===null){
				$columnData = $adb->getFieldsDefinition($this->result);
				$columnNames = array();
				foreach($columnData as $column){
					$columnNames[]=$column->name;
				}
				$copyFields = $columnNames;
			}
			
			$arr=array();
			foreach($this as $row){
				$rowArr = array();
				foreach($copyFields as $name){
					$rowArr[$name]=$row->$name;
				}
				$arr[]=$rowArr;
			}
			return $arr;
			$this->rewind();
		}
	}
	
	class SqlResultIteratorRow{

        public $data;

        function __construct($data){
			$this->data = $data;
		}
		
		function get($column){
			return $this->data[$column];
		}

        function __get($column){
            return $this->get($column);
        }
	}