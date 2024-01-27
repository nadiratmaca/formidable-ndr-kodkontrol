<?php


defined('ABSPATH') or die('Bu dosya doğrudan çağırılarak çalışmaz.');

/*******************************************
 * Yeni alan tipi oluştur
 */

add_filter('frm_available_fields', 'ndr_formidable_tcl_kodkontrol_field'); // Kısıtlı sürüm .
//add_filter( 'frm_pro_available_fields', 'ndr_formidable_add_pro_field' );  // Pro sürüm.
function ndr_formidable_tcl_kodkontrol_field($fields)
{
    $fields['type_kodkontrol'] = array(
        'name' => 'Kod Sorgulama',
        'icon' => 'frm_icon_font frm_pencil_icon', // Set the class for a custom icon here.

    );

    return $fields;
}


/******************************
 * Panel sürükle bırak ekranında görüntüle
 */
add_action('frm_display_added_fields', 'ndr_formidable_tcl_show_admin_field');
function ndr_formidable_tcl_show_admin_field($field)
{
    if ($field['type'] != 'type_kodkontrol') {    return false;  }


//var_dump($field);
     
    $return = '<div class="howto button-secondary frm_html_field" style="color:red" >Yandaki ayar panelinden Kod Alnını secin!.<div style="color:blue;">(Validasyon için Form ayarlarndan "Submit this form with AJAX" seçeneğini kapatınız.)</div></div>';

    if (isset($field['fld_kod']) and $field['fld_kod'] > 0) {
        /***
 public 'id' => string '22' (length=2)
public 'field_key' => string 'kod_alan_key' (length=12)
public 'name' => string 'Kodunuzu Giriniz' (length=16)
         */

        $select_field = (array) FrmField::getOne($field['fld_kod']);

        $return = '<div class="howto button-secondary frm_html_field" style="color:green; font-size:12px" >

<span style="padding:5px; margin:2px 5px;">Field ID: <strong>' . $select_field['id'] . '</strong></span>
<span style="padding:5px; margin:2px 5px;">Field Name: <strong>' . $select_field['name'] . '</strong></span>
<span style="padding:5px; margin:2px 5px;">Field Key: <strong>' . $select_field['field_key'] . '</strong></span>
<div style="color:blue;">(Validasyon için Form ayarlarndan "Submit this form with AJAX" seçeneğini kapatınız.)</div>
</div>';
    }

    //$field_name = 'item_meta['. $field['id'] .']';

?>
    <div class="frm_html_field_placeholder">
        <?php echo $return; ?>
    </div> <?php
        }
        /*******************************************
         * Alan için panel opsiyonlarını oluşturma
         */

        add_action('frm_field_options_form', 'ndr_formidable_tcl_options_form', 10, 3);
        function ndr_formidable_tcl_options_form($field, $display, $values)
        {

        @$isFormidable_pro = is_plugin_active( 'formidable-pro/formidable-pro.php' ) && FrmProAddonsController::get_readable_license_type() !="Lite";

            if ($field['type'] != 'type_kodkontrol') {
                return;
            }
            if (!isset($field['fld_kod'])) {
                $field['fld_kod'] = '';
            }


            $field['error_kodkontrol'] = isset($field['error_kodkontrol']) ? $field['error_kodkontrol']  : "Hatalı Kod girdiniz.";


            $fieldID = esc_attr($field['id']);



            ?>
    <tr>

        <table>
            <tr>
                <td id="fld_kod" data-label="KOD Giriş Alanı" data-value="<?php echo esc_attr($field['fld_kod']); ?>">
                <?php 
                if($isFormidable_pro){            
                ?>
                        <script>
                        function getTaxOrFieldSelection(fieldId) {
                        var formId = <?php echo $field["form_id"]; ?>;
                        if (formId) {
                        jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                        action: 'frm_get_field_selection',
                        field_id: <?php echo $fieldID; ?>,
                        field_name: "Select",
                        form_id: formId,
                        nonce: frmGlobal.nonce
                        },
                        success: function(msg) {
                        var container = jQuery('#' + fieldId);

                        msg = msg.replace('name="field_options[form_select_<?php echo $fieldID; ?>]"', 'id="field_' + fieldId + '" name="field_options[' + fieldId + '_<?php echo $fieldID; ?>]"');
                        msg = msg.replace('value="' + container.data().value + '"', 'value="' + container.data().value + '" selected ');

                        var label = '<label for="field_' + fieldId + '" class="howto">' + container.data().label + '</label>';
                        container.html(label + msg).show();
                        jQuery("option[value='<?php echo $fieldID; ?>']", container).remove();
                        }
                        });
                        }
                        }

                        getTaxOrFieldSelection("fld_kod");
                        </script>

               <?php  }else{  ?>


                <label for="field_options[fld_kod_<?php echo esc_attr( $field['id'] ) ?>]" class="howto">KOD alanı IDsi </label>
    <input type="text" name="field_options[fld_kod_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['fld_kod'] ); ?>" class="frm_long_input" id="fld_kod_<?php echo esc_attr( $field['id'] ) ?>"  />
   
  <?php } ?>

                </td>
            </tr>
        </table>

    <tr>
        <td>
            <label for="error_kodkontrol<?php echo $fieldID; ?>" class="howto">Hata mesajı</label>
            <textarea name="field_options[error_kodkontrol<?php echo $fieldID; ?>]" class="frm_long_input" id="error_kodkontrol<?php echo $fieldID; ?>" /><?php echo esc_attr($field['error_kodkontrol']); ?></textarea>
        </td>
    </tr>
   
<?php
        }

        /***
         * PAnel Opsionlarını Kaydet
         */


        add_filter('frm_update_field_options', 'ndr_formidable_tcl_update_field_options', 10, 3);
        function ndr_formidable_tcl_update_field_options($field_options, $field, $values)
        {
            if ($field->type != 'type_kodkontrol') {               return $field_options; }
                $defaults = array(
                'error_kodkontrol' => __('Hatalı KOD!. Bilgilerinizi kontrol ediniz.', "formidable"),
                'fld_kod' => null,
            );

            foreach ($defaults as $opt => $default)
                $field_options[$opt] = isset($values['field_options'][$opt . '_' . $field->id]) ? $values['field_options'][$opt . '_' . $field->id] : $default;

            $field_options["label"] = "none";
            return $field_options;
        }























