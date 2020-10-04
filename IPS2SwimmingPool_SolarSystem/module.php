<?
// Klassendefinition
class IPS2SwimmingPool_SolarSystem extends IPSModule 
{
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("PumpID", 0); // Pumpe
		$this->RegisterPropertyInteger("ThreeWayValve_ShortCircuit", 0); // Drei-Wege-Ventil im Kurzschlußbetrieb	
		$this->RegisterPropertyInteger("ThreeWayValve_Open", 0); // Drei-Wege-Ventil offen
		$this->RegisterPropertyInteger("ThreeWayValve_Runtime", 15); // Drei-Wege-Ventil Laufzeit
		$this->RegisterTimer("ThreeWayValve_Runtime", 0, 'IPS2SwimmingPoolSolarSystem_StateReset($_IPS["TARGET"]);');	
		
		//Status-Variablen anlegen		
		$this->RegisterVariableBoolean("Automatic", "Automatikbetrieb", "~Switch", 10);
		$this->EnableAction("Automatic");
		
		$this->RegisterVariableBoolean("PumpState", "Pumpenstatus", "~Switch", 10);
		//$this->RegisterVariableBoolean("PumpState", "Pumpenstatus", "~Switch", 10);
	
	}
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 		
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
            	$arrayElements[] = array("type" => "Label", "caption" => "Pumpen-Aktor-ID (Boolean)");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "PumpID", "caption" => "Aktor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
            	$arrayElements[] = array("type" => "Label", "caption" => "Drei-Wege-Ventil-Aktor-ID (Boolean) für den Kurzschlußbetrieb");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "ThreeWayValve_ShortCircuit", "caption" => "Aktor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Drei-Wege-Ventil-Aktor-ID (Boolean) für den geöffneten Betrieb");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "ThreeWayValve_Open", "caption" => "Aktor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Drei-Wege-Ventil-Laufzeit");
            	$arrayElements[] = array("type" => "IntervalBox", "name" => "ThreeWayValve_Runtime", "caption" => "s");
		
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			
		}
		else {
			$this->SetStatus(104);
			
		}	
	}       
	    
	// Beginn der Funktionen
	public function StateReset()
	{
		
		$this->SetTimerInterval("ThreeWayValve_Runtime", 0);
		
	}
}
?>
