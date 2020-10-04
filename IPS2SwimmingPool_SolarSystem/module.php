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
		$this->RegisterPropertyInteger("ThreeWayValve_ShortCircuitID", 0); // Drei-Wege-Ventil im Kurzschlußbetrieb	
		$this->RegisterPropertyInteger("ThreeWayValve_OpenID", 0); // Drei-Wege-Ventil offen
		$this->RegisterPropertyInteger("ThreeWayValve_Runtime", 15); // Drei-Wege-Ventil Laufzeit
		$this->RegisterTimer("ThreeWayValve_Runtime", 0, 'IPS2SwimmingPoolSolarSystem_StateReset($_IPS["TARGET"]);');	
		
		// Profile erstellen
		$this->RegisterProfileInteger("IPS2SwimmingPool.ThreeWayValve", "Information", "", "", 0, 2, 1);
		IPS_SetVariableProfileAssociation("IPS2SwimmingPool.ThreeWayValve", 0, "Unbekannt", "Garage", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2SwimmingPool.ThreeWayValve", 1, "Kurzschlußbetrieb", "Garage", 0x0000FF);
		IPS_SetVariableProfileAssociation("IPS2SwimmingPool.ThreeWayValve", 2, "Offen", "Garage", 0x00FF00);		
		
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
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "ThreeWayValve_ShortCircuitID", "caption" => "Aktor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Drei-Wege-Ventil-Aktor-ID (Boolean) für den geöffneten Betrieb");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "ThreeWayValve_OpenID", "caption" => "Aktor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Drei-Wege-Ventil-Laufzeit");
            	$arrayElements[] = array("type" => "IntervalBox", "name" => "ThreeWayValve_Runtime", "caption" => "s");
		
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Automatic") == true) {
			$this->DisableAction("PumpState");			
		}
		else {
			$this->EnableAction("PumpState");
		}
		
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			
		}
		else {
			$this->SetStatus(104);
			
		}	
	}       
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "Automatic":
			$this->SetValue($Ident, $Value);
			If ($Value == true) {
				$this->DisableAction("PumpState");			
			}
			else {
				$this->EnableAction("PumpState");
			}
	            	break;
		case "PumpState":
			If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ReadPropertyInteger("PumpID") > 9999)) {
				RequestAction($this->ReadPropertyInteger("PumpID"), $Value);
			}
	            	break;
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}
	
	// Beginn der Funktionen
	public function StateReset()
	{
		
		$this->SetTimerInterval("ThreeWayValve_Runtime", 0);
		
	}
	
	private function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 1);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 1)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);        
	}
}
?>
