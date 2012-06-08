<?php
/**
 * This class wraps a Metrodb_Dataitem and adds per row and per table user permissions.
 *
 * This class should be sub-classed for permanent data models.
 *
 * Usage:
 * class MyModel extends Metrodb_DataModel {
 *   public $tableName = 'my_model';
 *   public $useSearch = TRUE;
 *
 *   public function initDataItem() {
 *     parent::initDataItem();
 *     $this->dataItem->typeMap['created_on'] = 'timestamp';
 *     $this->dataItem->typeMap['published']  = 'int';
 *	   // allow published to be null
 *     $this->dataItem->_nuls[]               = 'published;
 * }
 *
 */
class Metrodb_Datamodel {

	public $tableName         = '';
	public $parentTable       = '';
	public $parentIdField     = '';
	public $searchIndexName   = 'global';
	public $useSearch         = FALSE;

	public $ownerIdField      = 'user_id';
	public $groupIdField      = 'group_id';
	public $sharingModeRead   = 'same-group';
	public $sharingModeCreate = 'same-group';
	public $sharingModeUpdate = 'same-owner';
	public $sharingModeDelete = 'same-owner';

	public $dataItem     = NULL;

	public function __construct($id=NULL) {
		if ($this->tableName !== '') {
			$this->initDataItem();
		}
		if ($id !== NULL) {
			$this->load($id);
		}
	}

	/**
	 * For any undefined attribute call get() on $this->dataItem
	 */
	public function __get($key) {
		return $this->dataItem->get($key);
	}

	/**
	 * For any undefined attribute call set() on $this->dataItem
	 */
	public function __set($key, $val) {
		return $this->dataItem->get($key, $val);
	}

	/**
	 * For any undefined method call that method on $this->dataItem
	 */
	public function __call($method, $args) {
		return call_user_func_array( array($this->dataItem, $method), $args);
	}

	/**
	 * Initialize the internal data item to a new Metrodb_Dataitem.
	 *
	 * Requires $this->tableName to be set.  Called from constructor
	 *
	 */
	public function initDataItem() {
		$this->dataItem = associate_getMeANew('dataitem', $this->tableName);
	}

	/**
	 * Load the internal dataItem using the $id
	 *
	 * @param $id int Unique id
	 */
	public function load($id) {
		$u = associate_getMeA('user');
		switch ($this->sharingModeRead) {
			//where group id in a list of group
			case 'same-group':
				$this->dataItem->andWhere($this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub($this->groupIdField, NULL, 'IS');
				break;

			case 'same-owner':
				$this->dataItem->andWhere($this->ownerIdField, $u->getUserId());
				$this->dataItem->orWhereSub($this->ownerIdField,0);
				break;

			case 'parent-group':
				$this->dataItem->_cols[] = $this->tableName.'.*';
				$this->dataItem->hasOne($this->parentTable, $this->parentIdField, 'Tparent', $this->parentIdField);
				$this->dataItem->andWhere('Tparent.'.$this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub('Tparent.'.$this->groupIdField, NULL);
				break;

			case 'parent-owner':
				$this->dataItem->_cols[] = $this->tableName.'.*';
				$this->dataItem->hasOne($this->parentTable, $this->parentIdField, 'Tparent', $this->parentIdField);
				$this->dataItem->andWhere('Tparent.'.$this->ownerIdField, $u->getUserId());
				break;

			case 'registered':
				if ($u->isAnonymous()) { return false; }
		}

		//load failed
		if (!$this->dataItem->load($id)) {
			return FALSE;
		}
		//load succeeded, but no permission
		return $this->dataItem->getPrimaryKey() != 0;
	}
}
