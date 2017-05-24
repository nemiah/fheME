<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class CCKalender extends CCPage implements iCustomContent {
    #private $loggedIn = false;
        
    private $oldMeetings = array();
    private $currentMeetings = array();
    private $upcommingMeetings = array();
        
    function __construct() {
            parent::__construct();

            $this->loadPlugin("ubiquitous", "Kalender");
            $this->loadPlugin("ubiquitous", "Todo");

            addClassPath(dirname(__FILE__));


            $this->loggedIn = Session::currentUser() != null;
    }
	
    function getStyle(){
            return "";
    }
    
    function getStyleFiles() {
        return array(
            /*"../../specifics/design/jquery.mobile-1.4.0.min.css", */
            "../../specifics/design/jquery-ui.min.css", 
            "../../specifics/design/styles_dt.css?rand=".date("Ymd"));
    }

    function getScriptFiles() {
        return array("../../specifics/js/dt.js?rand=".date("Ymd"), 
            "../../specifics/js/jquery-1.10.2.min.js"/*, 
            "../../specifics/js/jquery.mobile-1.4.0.min.js"*/);
    }
    
    public function getScript(){
            return "var CCKalender = {
				
            };";
    }
	
    function getLabel(){
            return "Kalender";
    }

    function getTitle(){
            return $this->getLabel();
    }
	
    function getCMSHTML() {
        
        //check for loggedin
        if(!$this->loggedIn){
            $content .= $this->getLoginScreen(); 
            echo $content;
            return false;
        }
        
        
        //reset current values
        $this->currentMeetings = array();
        $this->oldMeetings = array();
        $this->upcommingMeetings = array();
             
        
        //get new Data for Meetings from System       
        $this->getMeetingData();   
        
        //build screenTemplate
        echo $this->getScreenTemplate();        
        
        //process Meetings now and write to screen
        echo $this->getMeetingScreen();
    }
    
    
    /**
     * Build structure of screen for presenting the meetings
     * @return string
     */
    function getScreenTemplate() {
        $content = "template...";
        
        
        return $content;
    }
    
    function getMeetingData() {
        $currentTime = date("Hi");
        
        //create obj for today
        $D = new Datum();
        $D->normalize();

        $DE = clone $D;
        $DE->addDay();

        $T = new mTodoGUI();
        $K = $T->getCalendarData($D->time(), $DE->time(), Session::currentUser()->getID());
		
        while($DE->time() > $D->time()){
            $termine = $K->getEventsOnDay(date("dmY", $D->time()));

            //debug message
            echo "<div style='display:none'>";
            print_r($termine);
            echo "</div>";


            if($termine != null){
                foreach($termine AS $ev) {
                    foreach($ev AS $v){
                        
                        //start time in future = upcomming
                        if($v->getTime() > $currentTime) {
                            array_push($this->upcommingMeetings, $v);
                        //current events
                        } else if ($v->getTime() <= $currentTime && $v->getEndTime() > $currentTime) {
                            array_push($this->currentMeetings, $v);
                        } else {
                            array_push($this->oldMeetings, $v);
                        }
                        
                        //echo get_class($v).": ".$v->title()."<br>";
                    }
                }
            }
            $D->addDay();
        }
    }
    
    function getLoginScreen() {
        $T = new HTMLForm("login", array("benutzer", "password", "action"), "Anmeldung");

        $T->setValue("action", "login");
        $T->setType("action", "hidden");
        $T->setType("password", "password");

        $T->setLabel("password", "Passwort");

        $T->setSaveCustomerPage("Anmelden", "", false, "function(){ document.location.reload(); }");

        return $T;
    }
	
    function getMeetingScreen() {
        $content = "<div id='placeholder' style='display:'>";
        
        //inject old meetings
        $content .= "<div id='oldInject'>";
        if(count($this->oldMeetings) == 0) {
            $content .= "Keine vorherigen Meetings.";
        } else {
            $content .= "Old Meetings:";
            foreach($this->oldMeetings AS $aMeeting) {
                $content .= "<br />Name: ".$aMeeting->title();
            }
        }
        $content .= "</div>";
        
        //inject current meetings
        $content .= "<div id='currentInject'>";
        if(count($this->currentMeetings) == 0) {
            $content .= "Keine aktuellen Meetings geplant.";
        } else {
            $content .= "Current Meetings:";
            foreach($this->currentMeetings AS $aMeeting) {
                $content .= "<br />Name: ".$aMeeting->title();
            }
        }
        $content .= "</div>";
        
        //inject upcomming meetings
        $content .= "<div id='upcommingInject'>";
        if(count($this->upcommingMeetings) == 0) {
            $content .= "Keine weiteren Meetings geplant.";
        } else {
            $content .= "Upcomming Meetings:";
            foreach($this->upcommingMeetings AS $aMeeting) {
                $content .= "<br />Name: ".$aMeeting->title();
            }
        }
        $content .= "</div>";
        
        //close placeholder div
        $content .= "</div>";
        
        //start js injection
        $content .= "<script>alert('injected');</script>";
        
        return $content;
    }
    
    function handleForm($valuesAssocArray){
            switch($valuesAssocArray["action"]){
                    case "login":
                            if(!Users::login($valuesAssocArray["benutzer"], sha1($valuesAssocArray["password"]), "open3A"))
                                    Red::errorD("Benutzer/Passwort unbekannt");
                    break;

            }
    }
}

?>