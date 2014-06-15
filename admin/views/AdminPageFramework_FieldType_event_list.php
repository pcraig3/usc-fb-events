<?php
/**
 * Based off of Michael Uno's text field class in Admin Page Framework
 *
    * http://en.michaeluno.jp/admin-page-framework/
    * Copyright (c) 2013-2014 Michael Uno; Licensed MIT
 *
 */
if ( ! class_exists( 'AdminPageFramework_FieldType_event_list' ) ) :
    /**
     * Defines the events_list field type.
     */
    class AdminPageFramework_FieldType_event_list extends AdminPageFramework_FieldType_Base {

        /**
         * Defines the field type slugs used for this field type.
         */
        public $aFieldTypeSlugs = array( 'event_list', );

        /**
         * Defines the default key-values of this field type.
         * I don't need this to be configurable.
         *
         * @remark			$_aDefaultKeys holds shared default key-values defined in the base class.
         */
        protected $aDefaultKeys = array();

        /**
         * Returns the field type specific CSS rules.
         */
        public function _replyToGetStyles() {
            return "/* Event List Type */
				.admin-page-framework-field-event_list .admin-page-framework-field .admin-page-framework-input-label-container {
					/* CUSTOM CSS HERE */
				}
			" . PHP_EOL;
        }

        /**
         * Returns the output of the text input field.
         *
         * @since			2.1.5
         * @since			3.0.0			Removed unnecessary parameters.
         */
        public function _replyToGetField( $aField ) {

            return $aField['before_label']
                . "<div class='admin-page-framework-input-label-container'>"
                . "<div " . $this->generateAttributes( $aField['attributes'] ) . " ></div>"	// this method is defined in the base class
                . "</div>"
                . $aField['after_label'];
        }

    }
endif;