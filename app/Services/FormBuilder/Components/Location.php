<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;

class Location extends BaseComponent
{
    /**
     * Compile and echo the html element
     * @param  array $data [element data]
     * @param  stdClass $form [Form Object]
     * @return viod
     */
    public function compile($data, $form)
    {
        $elementName = $data['element'];
        $data = apply_filters('fluentform_rendering_field_data_'.$elementName, $data, $form);
        $rootName = $data['attributes']['name'];
        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';

        if (empty($data['attributes']['class'])) {
            $data['attributes']['class'] = '';
        }

        $data['attributes']['class'] .= $hasConditions;
        $data['attributes']['class'] .= ' ff-field_container ff-location-field-wrapper';
        if($containerClass = ArrayHelper::get($data, 'settings.container_class')) {
            $data['attributes']['class'] .= ' '.$containerClass;
        }
        $atts = $this->buildAttributes(
            ArrayHelper::except($data['attributes'], 'name')
        );
        $checked = $data['settings']['auto_detect'] ? 'checked' : '';
        $html = "<div {$atts}>";

        $html .= "<div class='ff-t-container'>";
            $html .= "<div class='ff-t-cell'>";
                $html .="<span>".$data['settings']['label']."</span>";
            $html .= "</div>";

            $html .= "<div class='ff-t-cell'>";
                $html .= "<input type='checkbox' ".esc_attr__($checked, 'fluentform')." class=".$this->makeElementId($data, $form).">";
                $html .="<span>".$data['settings']['auto_detect_label']."</span>";
            $html .= "</div>";
        $html .= "</div>";

        $html .= "<div class='ff-t-container'>";

        $labelPlacement = ArrayHelper::get($data, 'settings.label_placement');
        $labelPlacementClass = '';

        if ($labelPlacement) {
            $labelPlacementClass = ' ff-el-form-'.$labelPlacement;
        }

        foreach ($data['fields'] as $field) {
            $fieldName = $field['attributes']['name'];
            $field['attributes']['name'] = $rootName . '[' . $fieldName . ']';
            @$field['attributes']['class'] = trim(
                'ff-el-form-control ' .
                $field['attributes']['class']
            );
            if ($tabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex()) {
                $field['attributes']['tabindex'] = $tabIndex;
            }
            @$field['settings']['container_class'] .= $labelPlacementClass;

            $field['attributes']['id'] = $this->makeElementId($field, $form);
            $data[$field['element']] = $this->makeElementId($field, $form);
            $nameTitleClass= "";
            $elMarkup = "<input ".$this->buildAttributes($field['attributes']).">";

            $inputTextMarkup = $this->buildElementMarkup($elMarkup, $field, $form);
            $html .= "<div class='ff-t-cell {$nameTitleClass}'>{$inputTextMarkup}</div>";
        }

        $html .= "</div>";
        $html .= "</div>";

        $formId = "fluentform_" . $form->id;
        $checkBoxClass = $this->makeElementId($data, $form);
        $latId = $data['latitude'] ?? '';
        $latSelector = '#'.$formId . " #" . $latId;
        $longId = $data['longitude'] ?? '';
        $longSelector = '#'.$formId . " #" . $longId;
        $checkBoxSelector = "#" . $formId . " ." . $checkBoxClass;


        echo apply_filters('fluentform_rendering_field_html_'.$elementName, $html, $data, $form);
        ?>
        <script type="text/javascript">

            (function ($){
                $(document).on('click', "<?php echo $checkBoxSelector?>", function(){

                    if(this.checked){
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition((position) => {
                                $("<?php echo $latSelector?>").val(position.coords.latitude);
                                $("<?php echo $longSelector?>").val(position.coords.longitude);
                            }, (error) => {
                                this.checked = false;
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        alert("User denied the request for Geolocation. Please Allow location!")
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        alert("Location information is unavailable.")
                                        break;
                                    case error.TIMEOUT:
                                        alert("The request to get user location timed out.")
                                        break;
                                    case error.UNKNOWN_ERROR:
                                        alert("An unknown error occurred.")
                                        break;
                                }
                            });
                        }else{
                            this.checked = false;
                            alert('Your browser not support geolocation!')
                        }
                    }else {
                        this.checked = false
                        $("<?php echo $latSelector?>").val('');
                        $("<?php echo $longSelector?>").val('');
                    }
                })
                $(window).on('load', function (){
                    $("<?php echo $checkBoxSelector?>").each(function (){
                        if(this.checked){
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition((position) => {
                                    $("<?php echo $latSelector?>").val(position.coords.latitude);
                                    $("<?php echo $longSelector?>").val(position.coords.longitude);
                                }, (error) => {
                                    this.checked = false;
                                    switch(error.code) {
                                        case error.PERMISSION_DENIED:
                                            alert("User denied the request for Geolocation. Please Allow location!")
                                            break;
                                        case error.POSITION_UNAVAILABLE:
                                            alert("Location information is unavailable.")
                                            break;
                                        case error.TIMEOUT:
                                            alert("The request to get user location timed out.")
                                            break;
                                        case error.UNKNOWN_ERROR:
                                            alert("An unknown error occurred.")
                                            break;
                                    }
                                });
                            }else{
                                this.checked = false;
                                alert('Your browser not support geolocation!')
                            }
                        }else {
                            this.checked = false
                            $("<?php echo $latSelector?>").val('');
                            $("<?php echo $longSelector?>").val('');
                        }
                    })
                })
            })(jQuery)
        </script>
        <?php
    }
}
