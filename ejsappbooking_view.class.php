<?php

defined('MOODLE_INTERNAL') || die();
/*
require_once(__DIR__."/lib.php");
require_once(__DIR__.'/turnitintooltwo_form.class.php');
require_once(__DIR__.'/turnitintooltwo_submission.class.php');
*/

class ejsappbooking_view {
    
    public function __contruct($context){
        global $PAGE;
        
        $PAGE->set_context($context);
    }
    
    /**
     * Load the Javascript and CSS components for page.
     *
     * @global type $PAGE
     * @global type $CFG
     */
    public function load_page_components() {
        global $PAGE, $CFG;
        
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui', 'core');
        $PAGE->requires->jquery_plugin('ui-css', 'core');

        //$PAGE->requires->string_for_js('messageDelete', 'ejsappbooking');
        //$PAGE->requires->string_for_js('book_message', 'ejsappbooking');
        //$PAGE->requires->string_for_js('cancel', 'ejsappbooking');

        $PAGE->requires->js_call_amd('mod_ejsappbooking/ui','init', array($CFG->wwwroot . '/mod/ejsappbooking/controllers'));

        $PAGE->requires->css(new moodle_url('/mod/ejsappbooking/styles/ui.css'));

        $CFG->cachejs = false;
    }
    
    /**
     * Abstracted version of print_header() / header()
     *
     * @param string $url The URL of the page
     * @param string $title Appears at the top of the window
     * @param string $heading Appears at the top of the page
     * @param bool $return If true, return the visible elements of the header instead of echoing them.
     * @return mixed If return=true then string else void
     */
    public function print_header($url, $title = '', $heading = '') {
        global $PAGE, $OUTPUT;

        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($heading);
        
        echo $OUTPUT->header();
    }
    
    public function print_body($id, $intro, $remlabs, $practices, $tz, $tz_edit_url){
        
        global $OUTPUT;
        
        $this->print_intro($intro);
        $this->print_booking_form($id, $remlabs, $practices, $tz, $tz_edit_url);
        $this->print_mybooking_table();
        
    }
    
    public function print_footer(){
        global $OUTPUT;
        
        // Finish the page.
        echo $OUTPUT->footer();
    }
    
    function print_intro($intro){
        global $OUTPUT;

        echo html_writer::start_tag('div', array('class' => 'row '));
            echo html_writer::start_tag('div', array('class' => 'col-md-8 offset-md-1'));
                echo $OUTPUT->box($intro, 'generalbox mod_introbox', 'ejsappbookingintro');
            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    }
    
    function print_booking_form($id, $remlabs, $practices, $tz, $tz_edit_url){
        global $OUTPUT;
        
        // Header
        
        echo html_writer::start_tag('div', array('class' => 'row '));
            echo html_writer::start_tag('div', array('class' => 'col-md-8 offset-md-1'));
                echo $OUTPUT->heading(get_string('newreservation', 'ejsappbooking'));
            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        
        // Form      
      
        echo html_writer::start_tag('form', array('id' => 'bookingform', 'method' => 'get', 'action' => "/mod/ejsappbooking/controllers/add_booking.php?id=".$id));
        //
            // First row: lab and practice select

            echo html_writer::start_tag('div', array('class' => 'row selectores'));    

                echo html_writer::start_tag('div', array('class' => 'col-md-3 offset-md-1'));
                    echo get_string('rem_lab_selection', 'ejsappbooking') . ':&nbsp;&nbsp;'.'<br>';
                    echo $this->get_lab_select($remlabs);
                echo html_writer::end_tag('div');

                echo html_writer::start_tag('div', array('class' => 'col-md-4 offset-md-1'));
                    echo get_string('rem_prac_selection', 'ejsappbooking') . ':&nbsp;&nbsp;'.'<br>';
                    echo $this->get_practice_select($practices);
                echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');  /* row end */

            // Second row 

           echo html_writer::start_tag('div', array('class' => 'row'));

                // Left column: datepicker and timezone display

                echo html_writer::start_tag('div', array('class' => 'col-md-3 offset-md-1'));
                    echo '<span class="ui-icon ui-icon-calendar"></span> &nbsp;'.
                       get_string('date-select', 'ejsappbooking') . ':&nbsp;&nbsp;'.'<br>';
                    echo $OUTPUT->container('<div id="datepicker"></div>');   
                    echo '<p>' . $tz . '&nbsp; '. "<a href='$tz_edit_url' target='_blank' 
                        title='".get_string('time_zone_help', 'ejsappbooking')."'>"."<span class='ui-icon ui-icon-gear'></span>
                    </a></p></br>";
                echo html_writer::end_tag('div'); // column  end 

                // Right column: timepicker, notif area and submit button

                echo html_writer::start_tag('div', array('class' => 'col-md-3 offset-md-1'));
                    echo '<span class="ui-icon ui-icon-clock"></span>&nbsp;' . get_string('time-select', 'ejsappbooking').':'; 
                    echo $this->get_time_picker();
                    echo $this->get_notif_area();
                    // submit button
                    echo '<div id="submitwrap"><button id="booking_btn" name="bookingbutton" class="btn btn-secondary"'. 
                          'value="1" type="submit">' . get_string('book', 'ejsappbooking') . '</button></div>';
                echo html_writer::end_tag('div'); // end column
        
            echo html_writer::end_tag('div'); /* row end */        
        echo html_writer::end_tag('form');
    }
    
    function get_lab_select($remlabs){
        
        $select_lab = '<select name="labid" class="booking_select" data-previousindex="0" "> '; // onchange="this.form.submit()
        $currentlab = '';
        $i = 1;
        foreach ($remlabs as $remlab) {
            $labname[$remlab->id] = $remlab->name;
            if ($i == 1) {
                $labid = $remlab->id;
            }
            $select_lab .= '<option value="' . $remlab->id . '"';

            if ($labid == $remlab->id) {
                $select_lab .= 'selected="selected"';
                $currentlab = $labname[$remlab->id];
            }
            // $this->multilang->filter();
            $select_lab .= '>' . $labname[$remlab->id] . '</option>';
            $i++;
        }
        $select_lab .= '</select><br>';

        return $select_lab;
        
    }
    
    function get_practice_select($practices){
        
        $i = 1;
        $practid = 0;
        
        foreach ($practices as $practice) {
            // $multilang->filter(
            $labname[$practice->id] = $practice->practiceintro;
            if ($i == 1 && $practid == 0) {
                $practid = $practice->practiceid;
            }
            $i++;
        } // Select first practices as default;

        $selectedpractice = '';
        $select = '<select id="foo" name="practid" class="booking_select" data-previousindex="0" > ';
        $i = 1;
        
        foreach ($practices as $practice) {
            $labname[$practice->practiceid] = $practice->practiceintro;
            if ($i == 1 && $practid == 0) {
                $practid = $practice->practiceid;
            }
            $select .= '<option value="' . $practice->practiceid . '"';

            if ($practid == $practice->practiceid) {
                $select .= 'selected="selected"';
                $selectedpractice = $labname[$practice->practiceid];
            }
            // $multilang->filter()
            $select .= '>' . $labname[$practice->practiceid] . '</option>';
            
            $i++;
        }

        $select .= '</select>';

        return $select;
    }
    
    function get_notif_area(){
        
       $notif_area = '<div id="notif-area">'.
            '<div class="alert alert-primary slot-free" role="alert">'. 
                get_string('slot-free', 'ejsappbooking').'</div>'.
            '<div class="alert alert-dark slot-past error" role="alert">'. 
                get_string('slot-past', 'ejsappbooking').'</div>'.
            '<div class="alert alert-warning slot-busy error" role="alert">'. 
                get_string('slot-busy', 'ejsappbooking').'</div>'.
            '<div class="alert alert-danger plant-inactive error" role="alert">'. 
                get_string('plant-inactive', 'ejsappbooking').'</div>'.
            '<div class="alert alert-success plant-active" role="alert">'. 
                get_string('plant-active', 'ejsappbooking').'</div>'. 
            '<div id="notif" class="alert" role="alert">&nbsp;</div>'.
            '<div id="submit-error" class="alert" role="alert">'.
                get_string('submit-error', 'ejsappbooking').'</div>'.
            '<div class="alert submit-missing-field" role="alert">'.
                get_string('submit-missing-field', 'ejsappbooking').'</div>'.
        '</div>';

        return $notif_area;
    }
    
    function get_time_picker(){
        ob_start();
        include 'ejsappbooking_view_timepicker.php';
        return ob_get_clean();
    }
    
    function print_mybooking_table(){
        global $OUTPUT;

        echo html_writer::start_tag('div', array('class' => 'row'));
            echo html_writer::start_tag('div', array('class' => 'col-md-3 offset-md-1'));
                echo $OUTPUT->heading(get_string('mybookings', 'ejsappbooking'));
            echo html_writer::end_tag('div');

            echo html_writer::start_tag('div', array('class' => 'col-md-3'));
                echo '<nav><ul class="pagination" style="display: none">
                    <li class="page-item"><a class="page-link" href="#">&laquo; </a></li>
                    <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                </ul></nav>';
            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div'); /* row end */

        echo html_writer::start_tag('div', array('class' => 'row'));
            echo html_writer::start_tag('div', array('class' => 'col-md-7 offset-md-1 '));

            echo '<p e id="mybookings_notif" >'. get_string('mybookings_empty','ejsappbooking') . '</p>';

            echo '<table id="mybookings" class="table table-hover table-responsive-sm" style="display: none">
                    <thead><tr>
                       <th></th>
                       <th>'.get_string('date', 'ejsappbooking').'</th>              
                       <th>'.get_string('plant', 'ejsappbooking').'</th>
                       <th>'.get_string('hour', 'ejsappbooking').'</th>                        
                       <th>'.get_string('action', 'ejsappbooking').'</th>
                       </tr></thead>
                    <tbody></tbody>
                  </table>';

            echo '<div id="del-confirm" class="alert role="alert">' . get_string('delete-confirmation', 'ejsappbooking').'</div>';

            echo html_writer::end_tag('div'); // end col
        echo html_writer::end_tag('div'); // end row
    }
    

}
