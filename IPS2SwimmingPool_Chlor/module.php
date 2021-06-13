<?
// Klassendefinition
class IPS2SwimmingPool_Chlor extends IPSModule 
{
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("ORP_SensorID", 0); // pH Sensor
		$this->RegisterPropertyInteger("ORP_Target", 750);
		$this->RegisterPropertyFloat("MilliLiter", 20);
		$this->RegisterPropertyInteger("Timer_1", 30);
		$this->RegisterTimer("Timer_1", 0, 'IPS2SwimmingPool_CalculateChlor($_IPS["TARGET"]);');
		
		// Profile erstellen
		$this->RegisterProfileFloat("IPS2SwimmingPool.mV", "Electricity", "", " mV", -100000, +100000, 0.1, 3);
		
		//Status-Variablen anlegen		
		$this->RegisterVariableFloat("ORP", "ORP", "IPS2SwimmingPool.mV", 10);
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
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_1", "caption" => "Wiederholungszyklus in Minuten (30 Min Minimum)", "suffix" => "Minuten", "minimum" => 30);
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "ORP_Target", "caption" => "Ziel-pH-Wert", "suffix" => "pH", "minimum" => 720, "maximum" => 780 );
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "MilliLiter", "caption" => "Milliliter pro Defizit/mV", "suffix" => "ml", "minimum" => 0, "maximum" => 100);
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "SelectVariable", "name" => "ORP_SensorID", "caption" => "ORP-Sensor-ID"); 
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
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			If ($this->ReadPropertyInteger("ORP_SensorID") > 9999)
			{
				// Startbedingungen erfüllt
				$this->SetTimerInterval("Timer_1", ($this->ReadPropertyInteger("Timer_1") * 1000 * 60));

				$this->SetValue("ORP", GetValueFloat($this->ReadPropertyInteger("ORP_SensorID")));
				$this->SendDebug("ApplyChanges", "Startbedingungen erfuellt", 0);
				$this->SetStatus(102);
				// Registrierung für Änderung an den Variablen
				$this->RegisterMessage($this->ReadPropertyInteger("ORP_SensorID"), 10603);
			}
			else {
				Echo "Startbedingungen nicht erfuellt (fehlende Sensoren/Aktoren)!";
				$this->SetTimerInterval("Timer_1", 0);
				$this->SendDebug("ApplyChanges", "Startbedingungen nicht erfuellt!", 0);
				$this->SetStatus(202);
				$this->SetTimerInterval("Timer_1", 0);
			}		
		}
		else {
			$this->SetStatus(104);
			
		}	
	}       
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
			
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10603:
				// Änderung des pH-Wertes
				If ($SenderID == $this->ReadPropertyInteger("ORP_SensorID")) {
					$this->SetValue("ORP", GetValueFloat($this->ReadPropertyInteger("ORP_SensorID")));
				}
				break;
		}
    	}
	
	// Beginn der Funktionen
	public function CalculateChlor()
	{
		
		
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
	
	private function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 2);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 2)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
	        IPS_SetVariableProfileDigits($Name, $Digits);
	}
}
?>
