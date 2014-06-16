<?php
if ( ! class_exists( 'EventModifyCustomFieldType' ) ) :
    class EventModifyCustomFieldType extends AdminPageFramework_FieldType_submit {

        /**
         * Defines the field type slugs used for this field type.
         */
        public $aFieldTypeSlugs = array( 'event_modify', );

        /**
         * Defines the default key-values of this field type.
         *
         * @remark			$_aDefaultKeys holds shared default key-values defined in the base class.
         */
        protected $aDefaultKeys = array(
            'hidden_inputs' => array(
                    'eid',
                    'removed'
                ),
            'attributes'	=> array(
                'class'	=>	'button button-primary',
            ),
            'show_title_column' => false,
        );

        /**
         * Loads the field type necessary components.
         */
        public function _replyToFieldLoader() {
        }

        /**
         * Returns the field type specific JavaScript script.
         */
        public function _replyToGetScripts() {
            return "";
        }

        /**
         * Returns the field type specific CSS rules.
         */
        public function _replyToGetStyles() {
            return "";
        }

        /**
         * Returns the output of the field type.
         * @since			2.1.5			Moved from AdminPageFramework_FormField.
         */
        public function _replyToGetField( $aField ) {

            //see about changing the message
            $aField['label'] = $aField['label'] ? $aField['label'] : $this->oMsg->__( 'submit' );

            return parent::_replyToGetField( $aField );

        }

        /**
         * Returns extra output for the field.
         *
         * This is for the import field type that extends this class. The import field type cannot place the file input tag inside the label tag that causes a problem in FireFox.
         *
         * @since			3.0.0
         */
        protected function _getExtraFieldsBeforeLabel( &$aField ) {
            return '';
        }

        /**
         * Returns the output of hidden fields for this field type that enables custom submit buttons.
         * @since			3.0.0
         */
        protected function _getExtraInputFields( &$aField ) {

            $html_string = "";

            if( isset($aField['hidden_inputs']) && is_array($aField['hidden_inputs']) ) {

                foreach( $aField['hidden_inputs'] as &$input_name ) {
                    $html_string .= "<input type='hidden' "
                    //. "name='__submit[{$aField['input_id']}][input_id]' "
                    . "class='modify_{$input_name}' value=''"
                    . "/>";
                }
            }

            return $html_string;
        }

    }
endif;