<?php

class MVCategoryFields {

	protected $_term_id = null;
	protected static $_instance = null;

	protected function __construct() {

		$this->actions();
	}

	public static function get_instance() {

		if ( static::$_instance == null ) {
			static::$_instance = new static;
		}

		return static::$_instance;
	}

	public function actions() {

		add_action( "category_add_form_fields", function ( $term ) {
			$this->render_fields();
		} );
		add_action( "edit_category_form_fields", function ( $term ) {
			$this->render_fields( $term->term_id );
		} );
		add_action( "create_category", function ( $term_id ) {
			$this->_save_fields( $term_id );
		} );
		add_action( "edited_category", function ( $term_id ) {
			$this->_save_fields( $term_id );
		} );
		add_action( "delete_category", function ( $term_id ) {
			$this->_delete_meta_data( $term_id );
		} );
	}

	public function render_fields( $term_id = null ) {

		echo MVVideoField::render_field( $term_id );
		echo MVImageField::render_field( $term_id );

	}

	protected function _save_fields( $term_id ) {

		MVImageField::save( $term_id );
		MVVideoField::save( $term_id );
	}

	protected function _delete_meta_data( $term_id ) {

		MVCategoryFieldsDB::delete_term_meta( $term_id );
	}


}