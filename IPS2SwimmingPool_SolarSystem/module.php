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
		$this->RegisterTimer("ThreeWayValve_Runtime", 0, 'IPS2SwimmingPoolSolarSystem_ThreeWayValveStateReset($_IPS["TARGET"]);');	
		$this->RegisterPropertyInteger("Temperature_FlowID", 0); // Vorlauf-Temperatursensor
		$this->RegisterPropertyInteger("Temperature_CollectorAreaID", 0); // Kollektorflächen-Temperatursensor
		$this->RegisterPropertyInteger("Temperature_ShortCircuitID", 0); // Kurzschlusskreis-Temperatursensor
		$this->RegisterPropertyInteger("Temperature_ReturnID", 0); // Rücklauf-Temperatursensor
		
		// Profile erstellen
		$this->RegisterProfileInteger("IPS2SwimmingPool.ThreeWayValve", "Information", "", "", 0, 2, 1);
		IPS_SetVariableProfileAssociation("IPS2SwimmingPool.ThreeWayValve", 0, "Unbekannt", "Warning", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2SwimmingPool.ThreeWayValve", 1, "Kurzschlußbetrieb", "Repeat", 0x0000FF);
		IPS_SetVariableProfileAssociation("IPS2SwimmingPool.ThreeWayValve", 2, "Offen", "Return", 0x00FF00);		
		
		//Status-Variablen anlegen		
		$this->RegisterVariableBoolean("Automatic", "Automatikbetrieb", "~Switch", 10);
		$this->EnableAction("Automatic");
		
		$this->RegisterVariableBoolean("PumpState", "Pumpenstatus", "~Switch", 20);
		$this->RegisterVariableInteger("ThreeWayValve", "Drei-Wege-Ventil", "IPS2SwimmingPool.ThreeWayValve", 30);
	
	}
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Fehlende Sensorwerte/Aktoren!");
				
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
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Vorlauf-Temperatur-ID (Float)");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "Temperature_FlowID", "caption" => "Sensor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Kollektorflächen-Temperatur-ID (Float)");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "Temperature_CollectorAreaID", "caption" => "Sensor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Kurzschlusskreis-Temperatur-ID (Float)");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "Temperature_ShortCircuitID", "caption" => "Sensor"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Rücklauf-Temperatur-ID (Float)");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "Temperature_ReturnID", "caption" => "Sensor"); 
		
 		$arrayActions = array(); 
		$arrayActions[] = array("type" => "Label", "label" => "Test Center"); 
		$arrayActions[] = array("type" => "TestCenter", "name" => "TestCenter");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 	
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Automatic") == true) {
			$this->DisableAction("PumpState");
			$this->DisableAction("ThreeWayValve");
		}
		else {
			If ($this->ReadPropertyInteger("PumpID") > 9999) {
				$this->EnableAction("PumpState");
			}
			If (($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID") > 9999) AND ($this->ReadPropertyInteger("ThreeWayValve_OpenID") > 9999)) {
				$this->EnableAction("ThreeWayValve");
			}
		}
		
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			If (($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID") > 9999) 
			    AND ($this->ReadPropertyInteger("ThreeWayValve_OpenID") > 9999)
			    AND ($this->ReadPropertyInteger("PumpID") > 9999)
			    AND ($this->ReadPropertyInteger("Temperature_FlowID") > 9999)
			    AND ($this->ReadPropertyInteger("Temperature_CollectorAreaID") > 9999)
			    AND ($this->ReadPropertyInteger("Temperature_ShortCircuitID") > 9999)
			    AND ($this->ReadPropertyInteger("Temperature_ReturnID") > 9999) ) 
				{
					// Startbedingungen erfüllt
					$this->SetStatus(102);
					
				}
			else {
				$this->SetStatus(202);
			}
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
				$this->DisableAction("ThreeWayValve");			}
			else {
				$this->EnableAction("PumpState");
				$this->EnableAction("ThreeWayValve");
			}
	            	break;
		case "PumpState":
			If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ReadPropertyInteger("PumpID") > 9999)) {
				RequestAction($this->ReadPropertyInteger("PumpID"), $Value);
				$this->SetValue($Ident, $Value);
			}
	            	break;
		case "ThreeWayValve":
			If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID") > 9999) AND ($this->ReadPropertyInteger("ThreeWayValve_OpenID") > 9999)) {
				If ($Value == 1) {
					RequestAction($this->ReadPropertyInteger("ThreeWayValve_OpenID"), false);
					RequestAction($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID"), true);
				}
				elseif ($Value == 2) {
					RequestAction($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID"), false);
					RequestAction($this->ReadPropertyInteger("ThreeWayValve_OpenID"), true);
				}
				$this->SetTimerInterval("ThreeWayValve_Runtime", $this->ReadPropertyInteger("ThreeWayValve_Runtime") * 1000);
				$this->SetValue($Ident, $Value);
			}
	            	break;
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}
	
	// Beginn der Funktionen
	public function SolarSystemControl()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			// wenn die Kollektorflächen-Temperatur > der Vorlauftemperatur ist, soll die Pumpe laufen. Hysterese??
			$Temperature_CollectorArea = $this->GetValue($this->ReadPropertyInteger("Temperature_CollectorAreaID"));
			$Temperature_Flow = $this->GetValue($this->ReadPropertyInteger("Temperature_FlowID"));
			
			
		}
	}
	
	
	public function ThreeWayValveStateReset()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID") > 9999) AND ($this->ReadPropertyInteger("ThreeWayValve_OpenID") > 9999)) {
			RequestAction($this->ReadPropertyInteger("ThreeWayValve_ShortCircuitID"), false);
			RequestAction($this->ReadPropertyInteger("ThreeWayValve_OpenID"), false);
			$this->SetTimerInterval("ThreeWayValve_Runtime", 0);
		}
		
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
