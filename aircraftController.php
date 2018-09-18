<?php
if(!isset($_SESSION)) { session_start(); }
if(!isset($_SESSION['username']))
{
    header("Location: ./accessControl1.php");
}
    try
    {
        include 'connect.php';
        include_once 'models.php';
    }
    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
    {
        $error = 'Connection to database server connection failed';
        include 'views/error/error.html.php';
        exit();
    }
    //Function to clean data
    function clean_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = strip_tags($data);
        return $data;
    }
    //Decides what happens when the add button is clicked
    if(isset($_POST['submitToPdf']))
    {
        $incidentID = clean_input($_POST['incidentID']);
        include_once ('queries/selectQueries.php');
        include_once ('../build/aircraftpdf.php');
        include '../return.html.php';
    }
    elseif(isset ($_POST))
    {
       /*  echo("<pre>");
        var_dump($_POST);
        echo("</pre>"); */

        try
		{
            $AclNum = clean_input($_POST['AclNum']);
			$fileExtensions = ['jpeg','jpg','png','pdf','docx']; // Get all the file extensions
			foreach ($_FILES['file_upload']['tmp_name'] as $key => $tmp_name) // loop through files uploaded
			{												
					if (!empty($_FILES['file_upload']['tmp_name'][$key])) // check if each field is not empty
					{						
						$rootDir = (dirname(__DIR__));// get root directory
						$uploadDirectory = "/uploads/"; // current directory for uploading files											
						$errors = []; // Store all foreseen and unforseen errors here		
						// gets all file details by name, size, type
						$fileName = $_FILES['file_upload']['name'][$key];
						$fileSize = $_FILES['file_upload']['size'][$key];
						$fileTmpName  = $_FILES['file_upload']['tmp_name'][$key];
						$fileType = $_FILES['file_upload']['type'][$key];
						// $fileExtension = strtolower(end(explode('.',$fileName)));
						$fileExtension = pathinfo($_FILES["file_upload"]["name"][$key]);
						$fileExtension = $fileExtension['extension'];						
						// indicate path where file is being upload and change file name based on ACL num						
						$uploadPath = $rootDir . $uploadDirectory . $AclNum . "-" . basename($fileName); 						
						// below code -- if changing file name based on acl num is not preferred, this saves the file into the indicated path and retain the filename
						// $uploadPath = $rootDir . $uploadDirectory . basename($fileName);						
						// checks file extension
						if (! in_array($fileExtension,$fileExtensions)) 
						{
						$errors[] = "<br>This file extension is not allowed, please upload a JPEG or PNG file<br>";
						}
						// checks file size
						if ($fileSize > 5242880) 
						{
							$errors[] = "This file is more than 5MB. Sorry, it has to be less than or equal to 5MB<br>";
						}						
						// check if there are errors, if there's none -- it will upload the file.
						if (empty($errors)) 
						{
							
							$didUpload = move_uploaded_file($fileTmpName, $uploadPath);
							
							// check if it has been uploaded and gives a confirmation message
							if ($didUpload) 
							{
								echo "The file " . basename($fileName) . " has been uploaded at " . $uploadPath . "<br>";
							} 
							else 
							{
								echo "An error occurred somewhere. Try again or contact the admin<br>";
							}
						} 
						else // list all the errors
						{
							foreach ($errors as $error) 
							{
								echo $error . "These are the errors" . "\n";
							}
						}
					}
			}
		}
		catch (PDOException $e)
        {
            // Simple version of error message.
            $error = $errors;
            include 'error.html.php';
            exit();         
        }
        //Insert data to the person table
        try
        {
        // Contact Details
            // Clean the input
            $contactID = clean_input($_POST['contactID']);
            $firstName = clean_input($_POST['firstName']);
            $lastName =  clean_input($_POST['lastName']);
            $phoneFixed =  clean_input($_POST['phoneFixed']);
            $phoneMobile =  clean_input($_POST['phoneMobile']);
            $email =  clean_input($_POST['email']);
            // Create an a person array
            $person = array('id' => $contactID,'firstName' => $firstName, 'lastName' => $lastName, 'phoneFixed' => $phoneFixed, 'phoneMobile' => $phoneMobile, 'email' => $email); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
            updatePerson($pdo,$person);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Contact Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Insured Details
            // Clean the input
            $insuredID = clean_input($_POST['insuredID']);
            $insuredName = clean_input($_POST['insuredName']);
            $broker = clean_input($_POST['broker']);
            $finance = clean_input($_POST['finance']);
            $financeBool = clean_input($_POST['financeBool']);
            $GstNum = clean_input($_POST['GstNum']);
            $GstBool = clean_input($_POST['GstBool']);
            // Create an array for insured data
            $insured = array('id' => $insuredID,'insuredName' => $insuredName,'broker' => $broker,'finance' => $finance,'GstNum' => $GstNum, 'GstBool' => $GstBool, 'financeBool' => $financeBool);
            // Call the createInsured() method. This will return the id of the new insured item for use as a foreign key.
            //$insuredID = createInsured($pdo,$insured);
            updateInsured($pdo,$insured);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Insured Details error';
        include 'error.html.php';
        exit();
        }
        try{
        // Second Insurer Details - User selects a second insurer 
            // Clean the input
            $insuredID = clean_input($_POST['insuredID']);
            $insurerID = clean_input($_POST['insurerID']);
            $insurerRef = clean_input($_POST['insurerRef']);
            $policyNum = clean_input($_POST['policyNum']);
            $insurerRef2 = clean_input($_POST['insurerRef2']);
            $policyNum2 = clean_input($_POST['policyNum2']);
            $period = clean_input($_POST['period']);
            if(!empty(clean_input($_POST['sumInsured'])))
            {$sumInsured = clean_input($_POST['sumInsured']);}
            else
            {$sumInsured = NULL;}
            if(!empty(clean_input($_POST['excess'])))
            {$excess = clean_input($_POST['excess']);}
            else{$excess = NULL;}
            // Create an array for insured data
            $insurer = array('id' => $insurerID,'insuredID' => $insuredID, 'insurerRef' => $insurerRef,'policyNum' => $policyNum,'insurerRef2' => $insurerRef2,'policyNum2' => $policyNum2,'period' => $period,'sumInsured' => $sumInsured,'excess' => $excess);
            // Call the createInsured() method. This will return the id of the new insured item for use as a foreign key.
           // createInsurer($pdo,$insurer);  
           updateInsurer($pdo,$insurer);         
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Insurer Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Incident Details
            // Clean the input
            $incidentID = clean_input($_POST['incidentID']);
            $amountPassengers = clean_input($_POST['amountP']);
            $operator = clean_input($_POST['operator']);
            if(!empty(clean_input($_POST['date'])))
            {$date = clean_input($_POST['date']);}
            else{$date = NULL;}
            if(!empty(clean_input($_POST['time'])))
            {$time = clean_input($_POST['time']);}
            else{$time = NULL;}
            $location = clean_input($_POST['inLocation']);
            if(!empty(clean_input($_POST['latitude'])))
            {$latitude = clean_input($_POST['latitude']);}
            else{$latitude = NULL;}
            if(!empty(clean_input($_POST['latitude'])))
            {$longitude = clean_input($_POST['longitude']);}
            else{$longitude = NULL;}
            $incidentReport = clean_input($_POST['incidentReport']); /*attachment not needed*/
            $environmentalDamage = clean_input($_POST['environmentalDamage']); /*attachment not needed*/
            $confirmed = clean_input($_POST['confirmed']);
            if(!empty(clean_input($_POST['latitude'])))
            {$dated = clean_input($_POST['dated']);}
            else{$dated = NULL;}
            $position = clean_input($_POST['position']);
            $incident = array(
                'id' => $incidentID,
                'operator' => $operator,
                'amountPassengers' => $amountPassengers,
                'date' => $date,
                'time' => $time,
                'location' => $location,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'thirdPartyDamage' => 'thirdPartyDamage',
                'incidentReport' => $incidentReport,
                'holdLiableLetter' => 'holdLiableLetter',
                'environmentalDamage' => $environmentalDamage,
                'statementToOwner' => 'statementToOwner',
                'statementToCAA' => 'statementToCAA',
                'statementToPolice' => 'statementToPolice',
                'statementToTAIC' => 'statementToTAIC',
                'confirmed' => $confirmed,
                'dated' => $dated,
                'position' => $position
            );
            updateIncident($pdo,$incident);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Incident Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Equipment Details
            // Clean the input
            $equipmentID = clean_input($_POST['equipmentID']);
            $type = clean_input($_POST['type']);
            $make = clean_input($_POST['make']);
            $model = clean_input($_POST['model']);
            $serialNum = clean_input($_POST['serialNum']);
            if(!empty(clean_input($_POST['yearOfManufacture'])))
            {$yearOfManufacture = clean_input($_POST['yearOfManufacture']);}
            else{$yearOfManufacture = NULL;}
            // Create an array for insured data
            $equipment = array(
                'id' =>  $equipmentID,
                'type' => $type,
                'make' => $make,
                'model' => $model,
                'serialNum' => $serialNum,
                'yearOfManufacture' => $yearOfManufacture
            );
            updateEquipment($pdo,$equipment);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Equipment Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Engineer Details
            // Clean the input
            $engineerID = clean_input($_POST['engid']);
            $firstName = clean_input($_POST['engFirstName']);
            $lastName =  clean_input($_POST['engLastName']);
            $phoneFixed =  clean_input($_POST['engPhoneFixed']);
            $phoneMobile =  clean_input($_POST['engPhoneMobile']);
            $email =  clean_input($_POST['engEmail']);
            // Create an a person array
            $person = array('id' => $engineerID,'firstName' => $firstName, 'lastName' => $lastName, 'phoneFixed' => $phoneFixed, 'phoneMobile' => $phoneMobile, 'email' => $email); 
           updatePerson($pdo,$person);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Second Insured Details error';
        include 'error.html.php';
        exit();
        }
        //Insert data to the person table
        try
        {
        // thirdPartPerson Details
            // Clean the input
            $thirdPartPersonID = clean_input($_POST['thirdPartPersonID']);
            $firstName = clean_input($_POST['tpFirstName']);
            $lastName =  clean_input($_POST['tpLastName']);
            $phoneFixed =  clean_input($_POST['tpPhoneMobile']);
            $phoneMobile =  clean_input($_POST['tpPhoneFixed']);
            $email =  clean_input($_POST['tpEmail']);
            // Create an a person array
            $person = array('id' => $thirdPartPersonID,'firstName' => $firstName, 'lastName' => $lastName, 'phoneFixed' => $phoneFixed, 'phoneMobile' => $phoneMobile, 'email' => $email); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
            updatePerson($pdo,$person);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'thirdparty Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // thirdPartPerson Details
            // Clean the input
            $thirdPartPersonID = clean_input($_POST['thirdPartPersonID']);
            $company = "Aircraft";
            // Create an a person array
            $thirdParty = array('id' => $thirdPartPersonID,'company' => $company, 'thirdPartyType' =>  'Aircraft', 'thirdPartyRegistration' => 'Aircraft'); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
            updateThirdParty($pdo,$thirdParty);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'thirdparty Update error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Pilot Details
            // Clean the input
            $pilotPersonID = clean_input($_POST['pilotPersonID']);
            $firstName = clean_input($_POST['pFirstName']);
            $lastName =  clean_input($_POST['pLastName']);
            $phoneFixed =  clean_input($_POST['pPhoneFixed']);
            $phoneMobile =  clean_input($_POST['pPhoneMobile']);
            $email =  clean_input($_POST['pEmail']);
            // Create an a person array
            $person = array('id' => $pilotPersonID, 'firstName' => $firstName, 'lastName' => $lastName, 'phoneFixed' => $phoneFixed, 'phoneMobile' => $phoneMobile, 'email' => $email); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
           // $pilotPersonID = createPerson($pdo,$person);
           updatePerson($pdo,$person);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Pilot person Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
            // Pilot Details
            // Clean the input
            $pilotPersonID = clean_input($_POST['pilotPersonID']);
            if(!empty(clean_input($_POST['dateOfBirth'])))
            {$dateOfBirth = clean_input($_POST['dateOfBirth']);}
            else{$dateOfBirth = NULL;}
            $designation = clean_input($_POST['designation']);
            $licenceType = clean_input($_POST['licenceType']);
            $licenceNum = clean_input($_POST['licenceNum']);
            if(!empty(clean_input($_POST['hoursTotal'])))
            {$hoursTotal = clean_input($_POST['hoursTotal']);}
            else{$hoursTotal = NULL;}
            if(!empty(clean_input($_POST['hoursOnType'])))
            {$hoursOnType = clean_input($_POST['hoursOnType']);}
            else{$hoursOnType =NULL;}
            if(!empty(clean_input($_POST['hoursTotalLast90'])))
            {$hoursTotalLast90 = clean_input($_POST['hoursTotalLast90']);}
            else{$hoursTotalLast90 = NULL;}
            if(!empty(clean_input($_POST['hoursOnTypeLast90'])))
            {$hoursOnTypeLast90 = clean_input($_POST['hoursOnTypeLast90']);}
            else{$hoursOnTypeLast90 = NULL;}
            if(!empty(clean_input($_POST['lastFlightCheck'])))
            {$lastFlightCheck = clean_input($_POST['lastFlightCheck']);}
            else{$lastFlightCheck = NULL;}
            $medClass = clean_input($_POST['medClass']);
            if(!empty(clean_input($_POST['lastFlightCheck'])))
            {$medExpiry = clean_input($_POST['medExpiry']);}
            else{$medExpiry = NULL;}
            $reportedIncidents = clean_input($_POST['reportedIncidents']);
            // Create an array for insured data
            $pilot = array(
                'id' =>  $pilotPersonID, 
                'dateOfBirth' => $dateOfBirth,
                'designation' => $designation,
                'licenceType' => $licenceType,
                'licenceNum' => $licenceNum,
                'hoursTotal' => $hoursTotal,
                'hoursOnType' => $hoursOnType,
                'hoursTotalLast90' => $hoursTotalLast90,
                'hoursOnTypeLast90' => $hoursOnTypeLast90,
                'chemRatingDate' => 0,
                'lastFlightCheck' => $lastFlightCheck,
                'medClass' => $medClass,
                'medExpiry' => $medExpiry,
                'reportedIncidents' => $reportedIncidents
            );
            updatePilot($pdo,$pilot);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Pilot Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Aircraft Details
            // Clean the input
            $aircraftID = clean_input($_POST['aircraftID']);
            $registration = clean_input($_POST['registration']);
            $airworthinessCert = clean_input($_POST['airworthinessCert']);
            if(!empty(clean_input($_POST['AraExpires'])))
            {$AraExpires = clean_input($_POST['AraExpires']);}
            else{$AraExpires = NULL;}
            if(!empty(clean_input($_POST['AraIssueDate'])))
            {$AraIssueDate = clean_input($_POST['AraIssueDate']);}
            else{$AraIssueDate = NULL;}
            $AraIssuedBy = clean_input($_POST['AraIssuedBy']);
            $hoursTotal = clean_input($_POST['hoursTotal']);
            $hoursSinceCheck = clean_input($_POST['hoursSinceCheck']);
            $maintenanceProvider = clean_input($_POST['maintenanceProvider']);
            // Create an array for insured data
            $aircraft = array(
                'id' =>  $aircraftID,
                'registration' => $registration,
                'airworthinessCert' => $airworthinessCert,
                'AraAttached' => 'AraAttached',
                'AraExpires' => $AraExpires,
                'AraIssuedBy' => $AraIssuedBy,
                'AraIssueDate' => $AraIssueDate,
                'AraHoursTotal' => $hoursTotal,
                'AraHoursSinceCheck' => $hoursSinceCheck,
                'maintenanceProvider' => $maintenanceProvider
            );
            // Call the createAircraft() method. This will return the id of the new incident item for use as a foreign key.
            updateAircraft($pdo,$aircraft);

        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Second Insured Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
             // Left or Single Engine Details
            // Clean the input
            $LaircraftID = clean_input($_POST['aircraftID']);
            $Ltype =  clean_input($_POST['lseType']);
            $Lmodel =  clean_input($_POST['lseModel']);
            $LserialNum =  clean_input($_POST['lseSerialNum']);
            $LtimeSinceNew =  clean_input($_POST['lseTimeSinceNew']);
            if(!empty(clean_input($_POST['lseTimeSinceOverhaul'])))
            { $LoverhaulDate =  clean_input($_POST['lseTimeSinceOverhaul']);}
            else{$LoverhaulDate = NULL;}
        
            $leftEngine = array(
             'id' => $LaircraftID,
              'type' => $Ltype,
               'model' => $Lmodel, 
               'serialNum' => $LserialNum,
                'timeSinceNew' => $LtimeSinceNew, 
                'overhaulDate' => $LoverhaulDate); 
           
           // $leftEngineID = createFixedWing($pdo,$leftEngine);
           updateLeftEngine($pdo,$leftEngine);
        
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Left or Single Engine Details error';
        include 'error.html.php';
        exit();
        }
        try
        {    
        // Right Engine Details
            // Clean the input
            $RaircraftID = clean_input($_POST['aircraftID']);
            $Rtype =  clean_input($_POST['rseType']);
            $Rmodel =  clean_input($_POST['rseModel']);
            $RserialNum =  clean_input($_POST['rseSerialNum']);
            $RtimeSinceNew =  clean_input($_POST['rseTimeSinceNew']);
            if(!empty(clean_input($_POST['rseTimeSinceOverhaul'])))
            {$RoverhaulDate =  clean_input($_POST['rseTimeSinceOverhaul']);}
            else{$RoverhaulDate = NULL;}
            // Create an a person array
            $rightEngine = array('id' => $RaircraftID, 'type' => $Rtype, 'model' => $Rmodel, 'serialNum' => $RserialNum, 'timeSinceNew' => $RtimeSinceNew, 'overhaulDate' => $RoverhaulDate); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
           updateRightEngine($pdo,$rightEngine);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Right Engine Details error';
        include 'error.html.php';
        exit();
        }
        try
        {    
        // Left or Single Propeller Details
            // Clean the input
            $LPaircraftID = clean_input($_POST['aircraftID']);
            $LPtype =  clean_input($_POST['lspType']);
            $LPmodel =  clean_input($_POST['lspModel']);
            $LPserialNum =  clean_input($_POST['lspSerialNum']);
            $LPtimeSinceNew =  clean_input($_POST['lspTimeSinceNew']);
            if(!empty(clean_input($_POST['lspTimeSinceOverhaul'])))
            {$LPoverhaulDate =  clean_input($_POST['lspTimeSinceOverhaul']);}
            else{$LPoverhaulDate = NULL;}
            // Create an a person array
            $leftProp = array('id' => $LPaircraftID, 'type' => $LPtype, 'model' => $LPmodel, 'serialNum' => $LPserialNum, 'timeSinceNew' => $LPtimeSinceNew, 'overhaulDate' => $LPoverhaulDate); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
           updateLeftProp($pdo,$leftProp);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Left Prop Details error';
        include 'error.html.php';
        exit();
        }
        try
        {   
        // Right Propeller Details
            // Clean the input
            $RPaircraftID = clean_input($_POST['aircraftID']);
            $RPtype =  clean_input($_POST['rspType']);
            $RPmodel =  clean_input($_POST['rspModel']);
            $RPserialNum =  clean_input($_POST['rspSerialNum']);
            $RPtimeSinceNew =  clean_input($_POST['rspTimeSinceNew']);
            if(!empty(clean_input($_POST['rspTimeSinceOverhaul'])))
            {$RPoverhaulDate =  clean_input($_POST['rspTimeSinceOverhaul']);}
            else{$RPoverhaulDate = NULL;}
            // Create an a person array
            $rightProp= array('id' => $RPaircraftID, 'type' => $RPtype, 'model' => $RPmodel, 'serialNum' => $RPserialNum, 'timeSinceNew' => $RPtimeSinceNew, 'overhaulDate' => $RPoverhaulDate); 
            // Call the createPerson() method. This will return the id of the new person item for use as a foreign key.
           updateRightProp($pdo,$rightProp);
        }
        catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
        {
        $error = 'Right Prop Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Crew Passenger Details
            $passengerID =  clean_input($_POST['passengerID']);
            $firstName = clean_input($_POST['cpFirstName']);
            $lastName =  clean_input($_POST['cpLastName']);
            $phoneFixed =  clean_input($_POST['cpPhoneFixed']);
            $phoneMobile =  clean_input($_POST['cpPhoneMobile']);
            $email =  clean_input($_POST['cpEmail']);
            $person = array('id'=>$passengerID,'firstName' => $firstName, 'lastName' => $lastName, 'phoneFixed' => $phoneFixed, 'phoneMobile' => $phoneMobile, 'email' => $email); 
            updatePerson($pdo,$person);
        }
        catch (PDOEXCEPTION $e) 
        {
        $error = 'Crew Details error';
        include 'error.html.php';
        exit();
        }
        try
        {
        // Passenger 
            $passInjury = clean_input($_POST['injury']);
            $passengerID =  clean_input($_POST['passengerID']);
            $passenger = array('id' => $passengerID, 'injury' => $passInjury); 
            updatePassenger($pdo,$passenger);
        }
        catch (PDOEXCEPTION $e) 
        {
        $error = 'passenger update Details error';
        include 'error.html.php';
        exit();
        }
        //For loop to insert extra passengers dynamically
        if(isset($amountPassengers))
        {
            if($amountPassengers >1)
            { 
                for($i=1;$i<$amountPassengers;$i++)
                {
                    $value = $i+1;
                    try
                    {
                        $pID ="PassengerID{$value}";
                        $pN = "cpFirstName{$value}";
                        $pL = "cpLastName{$value}";
                        $pF = "cpPhoneFixed{$value}";
                        $pM = "cpPhoneMobile{$value}";
                        $pE = "cpEmail{$value}";
                        if(!empty($_POST[$pID]))
                        {
                            $passengerID =  clean_input($_POST[$pID]);
                            $firstName = clean_input($_POST[$pN]);
                            $lastName =  clean_input($_POST[$pL]);
                            $phoneFixed =  clean_input($_POST[$pF]);
                            $phoneMobile =  clean_input($_POST[$pM]);
                            $email =  clean_input($_POST[$pE]);
                            $person = array('id'=>$passengerID,'firstName' => $firstName, 'lastName' => $lastName, 'phoneFixed' => $phoneFixed, 'phoneMobile' => $phoneMobile, 'email' => $email); 
                            updatePerson($pdo,$person);
                        }
                    }
                    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
                    {
                            $error = 'Crew Details error';
                            include 'error.html.php';
                            exit();
                    }
                    try
                    {
                        if(!empty($_POST[$pID]))
                        {
                            $pI = "injury{$value}";
                            $passInjury = clean_input($_POST[$pI]);
                            $passengerID2 =  clean_input($_POST[$pID]);
                            $passenger = array('id' => $passengerID2, 'injury' => $passInjury); 
                            updatePassenger($pdo,$passenger);
                        }
                    }
                    catch (PDOEXCEPTION $e) 
                    {
                    $error = 'passenger2 update Details error';
                    include 'error.html.php';
                    exit();
                    }
                }
            }
        }
    try
    {
    // Weather 
        // Clean the input
        $weatherID = clean_input($_POST['weatherID']);
        $visibility = clean_input($_POST['visibility']);
        $windVelocity =  clean_input($_POST['windVelocity']);
        $windDirection =  clean_input($_POST['windDirection']);
        $temperature =  clean_input($_POST['temperature']);;
        $weather = array('id' => $weatherID,
         'visibility' => $visibility,
         'windVelocity' => $windVelocity, 
         'windDirection' => $windDirection,
         'temperature' => $temperature); 
         updateWeather($pdo,$weather);
    }
    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
    {
    $error = 'Weather insert error';
    include 'error.html.php';
    exit();
    }
     try
    {
    // Aircraft weight and balance details
        // Clean the input
        $weightID = clean_input($_POST['weightID']);
        if(!empty(clean_input($_POST['authorisedTakeOffWeight'])))
        {$authorisedTakeOffWeight = clean_input($_POST['authorisedTakeOffWeight']);}
        else{$authorisedTakeOffWeight = NULL;}
        if(!empty(clean_input($_POST['takeOffWeight'])))
        {$takeOffWeight =  clean_input($_POST['takeOffWeight']);}
        else{$takeOffWeight = NULL;}
        if(!empty(clean_input($_POST['weightAtOccurance'])))
        {$weightAtOccurance =  clean_input($_POST['weightAtOccurance']);}
        else{$weightAtOccurance = NULL;}
        $fuelAtDeparture =  clean_input($_POST['fuelAtDeparture']);
        $fuelAtOccurance =  clean_input($_POST['fuelAtOccurance']);
        $lastRefuel =  clean_input($_POST['lastRefuel']);
        $waterCheck =  clean_input($_POST['waterCheck']);
       
        $weight = array('id' => $weightID,
        'authorisedTakeOffWeight' => $authorisedTakeOffWeight,
        'takeOffWeight' => $takeOffWeight, 
        'weightAtOccurance' => $weightAtOccurance,
        'fuelAtDeparture' => $fuelAtDeparture, 
        'fuelAtOccurance' => $fuelAtOccurance,
        'lastRefuel' => $lastRefuel, 
        'waterCheck' => $waterCheck); 
        updateWeight($pdo,$weight);
    }
    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
    {
    $error = 'Weight Details error';
    include 'error.html.php';
    exit();
    }
    try
    {
    // Comments Aircraft and circumstances get the same incident number
        // Clean the input
        $CircumstanceID = clean_input($_POST['CircumstanceID']);
        $circumstances = clean_input($_POST['circumstances']);
        // Adding comments to the database
        $circumstance = array('id' => $CircumstanceID,
        'comment' => $circumstances); 
        updateCircumstance($pdo,$circumstance);
    }
    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
    {
    $error = 'Circumstances Comments update error';
    include 'error.html.php';
    exit();
    }
    try
    {
    // Comments Aircraft and circumstances get the same incident number
        // Clean the input
        $DamageID =  clean_input($_POST['DamageID']);
        $aircraftDamage =  clean_input($_POST['aircraftDamage']);
        $Damage = array('id' => $DamageID,
        'comment' => $aircraftDamage); 
        updateDamage($pdo,$Damage);
    }
    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
    {
    $error = 'Circumstances Comments insert error';
    include 'error.html.php';
    exit();
    }
    try
    {
    // Aircraft-Flight Details
        // Clean the input
        $flightDetailsID = clean_input($_POST['flightDetailsID']);
        $origin = clean_input($_POST['origin']);
        $equipmentID = clean_input($_POST['equipmentID']);
        $destination =  clean_input($_POST['destination']);
        $purpose =  clean_input($_POST['purpose']);
        if(!empty(clean_input($_POST['flightRules'])))
        {$flightRules =  clean_input($_POST['flightRules']);}
        else{$flightRules = NULL;}
        $authorisation =  clean_input($_POST['authorisation']);
        $flight = array('id' => $flightDetailsID,
        'origin' => $origin,
        'destination' => $destination, 
        'purpose' => $purpose,
        'flightRules' => $flightRules, 
        'authorisation' => $authorisation);
        updateFlight($pdo,$flight);
    }
    catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
    {
    $error = 'Aircraft Flight Details error';
    include 'error.html.php';
    exit();
    }
       //switch to direct user when they complete the form
       try
       {
       $userID = clean_input($_POST['userID']);
       switch($userID)
       {
           case "admin":
           include '../return.html.php';
           break;
           case "client":
           include '../views/common/exit.html.php';
           break;
           case "maint":
           //include '../views/common/exit.html.php';
           include '../return.html.php';
           break;
       }
       }
       catch (PDOEXCEPTION $e) //output if connecting to the database is incorrect
       {
       $error = 'switch Details error';
       include './views/error/error.html.php';
       exit();
       }
    }
    elseif(isset($client) ==true)
    {
        include_once ($selectQueriesUrl . 'selectQueries.php');
        $page = 'aircraft';
        include_once (dirname(__DIR__) . '/mainC.php'); 
    }
    elseif(isset($MainClient) ==true)
    {
        include_once ($selectQueriesUrl . 'selectQueries.php');
        $page = 'aircraft';
        include_once (dirname(__DIR__) . '/mainM.php'); 
    }   
    elseif(isset($admin) ==true)
    {
            //If the admin clicks on the link the following happens -- this allow the files to be updated
            /* Queries to get more information to display in values on the different sections on the form */   
                    //Query Incident table to get your Required ID
                    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $breakdown = parse_url($url); 
                    //var_dump($breakdown);
                    $schem = $breakdown['scheme'];
                    $host = $breakdown['host'];
                    $path = $breakdown['path'];
                    $path = explode("/",$path);   
                    unset($path[sizeof($path)-1]);
                    $path = implode("/",$path);
                    $Newurl = $schem."://".$host.$path;                                
                    include_once ($selectQueriesUrl . 'selectQueries.php');
                    $client = "/clientForm.php?id=";
                    $maint = "/mainForm.php?id=";
                    $directoryClient = $Newurl.$client.$hashedID;
                    $directoryMaint = $Newurl.$maint.$hashedID;
                    $page = 'aircraft';
                   // move to the home dir and find main.php
                   include_once (dirname(__DIR__) . '/main.php'); 
    } 
    else
    {
        try
        {
                    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $breakdown = parse_url($url); 
                    //var_dump($breakdown);
                    $schem = $breakdown['scheme'];
                    $host = $breakdown['host'];
                    $path = $breakdown['path'];
                    $path = explode("/",$path);   
                    unset($path[sizeof($path)-1]);
                    $path = implode("/",$path);
                    $Newurl = $schem."://".$host.$path;                                
                    include_once ($selectQueriesUrl . 'selectQueries.php');
                    $client = "/clientForm.php?id=";
                    $maint = "/mainForm.php?id=";
                    $directoryClient = $Newurl.$client.$hashedID;
                    $directoryMaint = $Newurl.$maint.$hashedID;
                    $page = 'aircraft';
                   // move to the home dir and find main.php
                   include_once (dirname(__DIR__) . '/main.php');
        }
        catch (PDOException $e)
        {

            $error = 'Select the incident  failed';
            include './views/error/error.html.php';
            exit();         
        }
    }
?>
