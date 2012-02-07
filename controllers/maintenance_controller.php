<?php
class MaintenanceController extends AppController {
	public $uses = array('Product','User','Cpu','ProductsTag','Tag');
	
	public $permissions = array(
		'admin_chipsets' => '*',
		'admin_counter_check' => 1,
	);
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	//Returns Tag Group Select.
	public function admin_tag_select($tagGroupId) {
		$this->loadModel('Tag');
		$tags = $this->Tag->findAllByTagGroupId($tagGroupId);
		$this->set(compact('tags'));
	}
	
	//updates counter caches
	public function admin_counter_check() {
		$counterTemplate =
           "UPDATE :atable AS :amodel
           SET :counter = (
               SELECT COUNT(:model.id)
               FROM :table AS :model
               WHERE :model.:foreignkey = :amodel.id :counterscope
            )";

		$updates = array();

		$models = Configure::listObjects('model') ;

		foreach ($models as $model) {
			$this->loadModel($model);
			$mainTable = Inflector::tableize($model);
			if (!empty($this->$model->belongsTo)) {
				foreach ($this->$model->belongsTo as $assocModel => $def) {
					if (isset($def['counterCache']) && $def['counterCache']) {
						$assocTable = Inflector::tableize($def['className']);
						$assocClass = $def['className'];
						$foreignKey = $def['foreignKey'];
						$counter = strtolower($model) . '_count';
						
						$counterScope = '';
						if(isset($def['counterScope'])) {
							foreach($def['counterScope'] as $key => $value) {
								$counterScope.= 'AND '.$key.' = ' .$value;
							}
						}
						
						$query = String::insert($counterTemplate,
							array(
	                            'table' => $mainTable,
	                            'model' => $model,
	                            'atable' => $assocTable,
	                            'amodel' => $assocModel,
	                            'foreignkey' => $foreignKey,
	                            'counter' => $counter,
								'counterscope' => $counterScope
							));
						$result = $this->Product->query($query);
						$affectedRows = $this->Product->getAffectedRows();
						$updates[] = array(
                            'query' => $query,
                            'affected_rows' => $affectedRows
						);
					}
				}
			}
		}
		$this->set(compact('updates'));

	} // end function Counter checks
}