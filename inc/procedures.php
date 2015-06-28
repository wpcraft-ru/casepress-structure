<?php

/*
Подразделения
*/
class ProcedureCP_Singletone {
private static $_instance = null;

private function __construct() {
    
    add_action('init', array($this, 'cpt'));

}




function cpt() {

	$labels = array(
		'name'                => __( 'Procedures', 'casepress' ),
		'singular_name'       => __( 'Procedure', 'casepress' ),
		'menu_name'           => __( 'Procedures', 'casepress' ),
		'parent_item_colon'   => __( 'Parent', 'casepress' ),
		'all_items'           => __( 'Procedures', 'casepress' ),
		'view_item'           => __( 'View', 'casepress' ),
		'add_new_item'        => __( 'Add Procedure', 'casepress' ),
		'add_new'             => __( 'New Procedure', 'casepress' ),
		'edit_item'           => __( 'Edit', 'casepress' ),
		'update_item'         => __( 'Update', 'casepress' ),
		'search_items'        => __( 'Search', 'casepress' ),
		'not_found'           => __( 'Not found', 'casepress' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'casepress' ),
	);
	$args = array(
		'description'         => __( 'Description Procedure', 'casepress' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'comments', 'page-attributes', ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=branch',
		'show_in_admin_bar'   => false,
		'menu_position'       => 20,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'procedure', $args );

}

    
    
protected function __clone() {
	// ограничивает клонирование объекта
}

static public function getInstance() {
	if(is_null(self::$_instance))
	{
	self::$_instance = new self();
	}
	return self::$_instance;
}

} $ProcedureCP = ProcedureCP_Singletone::getInstance();