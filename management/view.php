<?php
        /* This Source Code Form is subject to the terms of the Mozilla Public
         * License, v. 2.0. If a copy of the MPL was not distributed with this
         * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

        // Include required functions file
        require_once(realpath(__DIR__ . '/../includes/functions.php'));
        require_once(realpath(__DIR__ . '/../includes/authenticate.php'));
	require_once(realpath(__DIR__ . '/../includes/display.php'));

        // Add various security headers
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");

        // If we want to enable the Content Security Policy (CSP) - This may break Chrome
        if (CSP_ENABLED == "true")
        {
                // Add the Content-Security-Policy header
                header("Content-Security-Policy: default-src 'self'; script-src 'unsafe-inline'; style-src 'unsafe-inline'");
        }

        // Session handler is database
        if (USE_DATABASE_FOR_SESSIONS == "true")
        {
		session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc');
        }

        // Start the session
	session_set_cookie_params(0, '/', '', isset($_SERVER["HTTPS"]), true);
        session_start('SimpleRisk');

        // Include the language file
        require_once(language_file());

        require_once(realpath(__DIR__ . '/../includes/csrf-magic/csrf-magic.php'));

        // Check for session timeout or renegotiation
        session_check();

	// Default is no alert
	$alert = false;

        // Check if access is authorized
        if (!isset($_SESSION["access"]) || $_SESSION["access"] != "granted")
        {
                header("Location: ../index.php");
                exit(0);
        }

        // Check if a risk ID was sent
        if (isset($_GET['id']))
        {
                $id = htmlentities($_GET['id'], ENT_QUOTES, 'UTF-8', false);

        	// If team separation is enabled
        	if (team_separation_extra())
        	{
                	//Include the team separation extra
                	require_once(realpath(__DIR__ . '/../extras/separation/index.php'));

			// If the user should not have access to the risk
			if (!extra_grant_access($_SESSION['uid'], $id))
			{
                                // Redirect back to the page the workflow started on
                                header("Location: " . $_SESSION["workflow_start"]);
				exit(0);
			}
        	}

        	// If the classic risk was updated and the user has the ability to modify the risk
        	if (isset($_POST['update_classic']) && isset($_SESSION["modify_risks"]) && $_SESSION["modify_risks"] == 1)
        	{
                        $CLASSIC_likelihood = (int)$_POST['likelihood'];
                        $CLASSIC_impact = (int)$_POST['impact'];

			// Update the risk scoring
			update_classic_score($id, $CLASSIC_likelihood, $CLASSIC_impact);
                }
                // If the cvss risk was updated and the user has the ability to modify the risk
                else if (isset($_POST['update_cvss']) && isset($_SESSION["modify_risks"]) && $_SESSION["modify_risks"] == 1)
                {
                	$AccessVector = $_POST['AccessVector'];
                	$AccessComplexity = $_POST['AccessComplexity'];
                	$Authentication = $_POST['Authentication'];
                	$ConfImpact = $_POST['ConfImpact'];
                	$IntegImpact = $_POST['IntegImpact'];
                	$AvailImpact = $_POST['AvailImpact'];
                	$Exploitability = $_POST['Exploitability'];
                	$RemediationLevel = $_POST['RemediationLevel'];
                	$ReportConfidence = $_POST['ReportConfidence'];
                	$CollateralDamagePotential = $_POST['CollateralDamagePotential'];
                	$TargetDistribution = $_POST['TargetDistribution'];
                	$ConfidentialityRequirement = $_POST['ConfidentialityRequirement'];
                	$IntegrityRequirement = $_POST['IntegrityRequirement'];
                	$AvailabilityRequirement = $_POST['AvailabilityRequirement'];

                        // Update the risk scoring
			update_cvss_score($id, $AccessVector, $AccessComplexity, $Authentication, $ConfImpact, $IntegImpact, $AvailImpact, $Exploitability, $RemediationLevel, $ReportConfidence, $CollateralDamagePotential, $TargetDistribution, $ConfidentialityRequirement, $IntegrityRequirement, $AvailabilityRequirement);
                }
                // If the dread risk was updated and the user has the ability to modify the risk
                else if (isset($_POST['update_dread']) && isset($_SESSION["modify_risks"]) && $_SESSION["modify_risks"] == 1)
                {
                        $DREADDamagePotential = (int)$_POST['DamagePotential'];
                        $DREADReproducibility = (int)$_POST['Reproducibility'];
                        $DREADExploitability = (int)$_POST['Exploitability'];
                        $DREADAffectedUsers = (int)$_POST['AffectedUsers'];
                        $DREADDiscoverability = (int)$_POST['Discoverability'];

			// Update the risk scoring
			update_dread_score($id, $DREADDamagePotential, $DREADReproducibility, $DREADExploitability, $DREADAffectedUsers, $DREADDiscoverability);
		}
                // If the owasp risk was updated and the user has the ability to modify the risk
                else if (isset($_POST['update_owasp']) && isset($_SESSION["modify_risks"]) && $_SESSION["modify_risks"] == 1)
		{
                        $OWASPSkillLevel = (int)$_POST['SkillLevel'];
                        $OWASPMotive = (int)$_POST['Motive'];
                        $OWASPOpportunity = (int)$_POST['Opportunity'];
                        $OWASPSize = (int)$_POST['Size'];
                        $OWASPEaseOfDiscovery = (int)$_POST['EaseOfDiscovery'];
                        $OWASPEaseOfExploit = (int)$_POST['EaseOfExploit'];
                        $OWASPAwareness = (int)$_POST['Awareness'];
                        $OWASPIntrusionDetection = (int)$_POST['IntrusionDetection'];
                        $OWASPLossOfConfidentiality = (int)$_POST['LossOfConfidentiality'];
                        $OWASPLossOfIntegrity = (int)$_POST['LossOfIntegrity'];
                        $OWASPLossOfAvailability = (int)$_POST['LossOfAvailability'];
                        $OWASPLossOfAccountability = (int)$_POST['LossOfAccountability'];
                        $OWASPFinancialDamage = (int)$_POST['FinancialDamage'];
                        $OWASPReputationDamage = (int)$_POST['ReputationDamage'];
                        $OWASPNonCompliance = (int)$_POST['NonCompliance'];
                        $OWASPPrivacyViolation = (int)$_POST['PrivacyViolation'];

			// Update the risk scoring
			update_owasp_score($id, $OWASPSkillLevel, $OWASPMotive, $OWASPOpportunity, $OWASPSize, $OWASPEaseOfDiscovery, $OWASPEaseOfExploit, $OWASPAwareness, $OWASPIntrusionDetection, $OWASPLossOfConfidentiality, $OWASPLossOfIntegrity, $OWASPLossOfAvailability, $OWASPLossOfAccountability, $OWASPFinancialDamage, $OWASPReputationDamage, $OWASPNonCompliance, $OWASPPrivacyViolation);
		}
                // If the custom risk was updated and the user has the ability to modify the risk
                else if (isset($_POST['update_custom']) && isset($_SESSION["modify_risks"]) && $_SESSION["modify_risks"] == 1)
		{
			$custom = (float)$_POST['Custom'];

			// Update the risk scoring
			update_custom_score($id, $custom);
		}

                // Get the details of the risk
                $risk = get_risk_by_id($id);

		// If the risk was found use the values for the risk
		if (count($risk) != 0)
		{
                	$status = htmlentities($risk[0]['status'], ENT_QUOTES, 'UTF-8', false);
                	$subject = htmlentities(stripslashes($risk[0]['subject']), ENT_QUOTES, 'UTF-8', false);
			$reference_id = htmlentities(stripslashes($risk[0]['reference_id']), ENT_QUOTES, 'UTF-8', false);
			$regulation = htmlentities($risk[0]['regulation'], ENT_QUOTES, 'UTF-8', false);
			$control_number = htmlentities($risk[0]['control_number'], ENT_QUOTES, 'UTF-8', false);
			$location = htmlentities($risk[0]['location'], ENT_QUOTES, 'UTF-8', false);
                	$category = htmlentities($risk[0]['category'], ENT_QUOTES, 'UTF-8', false);
                	$team = htmlentities($risk[0]['team'], ENT_QUOTES, 'UTF-8', false);
                	$technology = htmlentities($risk[0]['technology'], ENT_QUOTES, 'UTF-8', false);
                	$owner = htmlentities($risk[0]['owner'], ENT_QUOTES, 'UTF-8', false);
                	$manager = htmlentities($risk[0]['manager'], ENT_QUOTES, 'UTF-8', false);
                	$assessment = htmlentities(stripslashes($risk[0]['assessment']), ENT_QUOTES, 'UTF-8', false);
                	$notes = htmlentities(stripslashes($risk[0]['notes']), ENT_QUOTES, 'UTF-8', false);
			$submission_date = htmlentities($risk[0]['submission_date'], ENT_QUOTES, 'UTF-8', false);
			$mitigation_id = htmlentities($risk[0]['mitigation_id'], ENT_QUOTES, 'UTF-8', false);
			$mgmt_review = htmlentities($risk[0]['mgmt_review'], ENT_QUOTES, 'UTF-8', false);
			$calculated_risk = htmlentities($risk[0]['calculated_risk'], ENT_QUOTES, 'UTF-8', false);
			$next_review = htmlentities($risk[0]['next_review'], ENT_QUOTES, 'UTF-8', false);
			$color = get_risk_color($id);

			$scoring_method = htmlentities($risk[0]['scoring_method'], ENT_QUOTES, 'UTF-8', false);
			$CLASSIC_likelihood = htmlentities($risk[0]['CLASSIC_likelihood'], ENT_QUOTES, 'UTF-8', false);
			$CLASSIC_impact = htmlentities($risk[0]['CLASSIC_impact'], ENT_QUOTES, 'UTF-8', false);
			$AccessVector = htmlentities($risk[0]['CVSS_AccessVector'], ENT_QUOTES, 'UTF-8', false);
			$AccessComplexity = htmlentities($risk[0]['CVSS_AccessComplexity'], ENT_QUOTES, 'UTF-8', false);
			$Authentication = htmlentities($risk[0]['CVSS_Authentication'], ENT_QUOTES, 'UTF-8', false);
			$ConfImpact = htmlentities($risk[0]['CVSS_ConfImpact'], ENT_QUOTES, 'UTF-8', false);
			$IntegImpact = htmlentities($risk[0]['CVSS_IntegImpact'], ENT_QUOTES, 'UTF-8', false);
			$AvailImpact = htmlentities($risk[0]['CVSS_AvailImpact'], ENT_QUOTES, 'UTF-8', false);
			$Exploitability = htmlentities($risk[0]['CVSS_Exploitability'], ENT_QUOTES, 'UTF-8', false);
			$RemediationLevel = htmlentities($risk[0]['CVSS_RemediationLevel'], ENT_QUOTES, 'UTF-8', false);
			$ReportConfidence = htmlentities($risk[0]['CVSS_ReportConfidence'], ENT_QUOTES, 'UTF-8', false);
			$CollateralDamagePotential = htmlentities($risk[0]['CVSS_CollateralDamagePotential'], ENT_QUOTES, 'UTF-8', false);
			$TargetDistribution = htmlentities($risk[0]['CVSS_TargetDistribution'], ENT_QUOTES, 'UTF-8', false);
			$ConfidentialityRequirement = htmlentities($risk[0]['CVSS_ConfidentialityRequirement'], ENT_QUOTES, 'UTF-8', false);
			$IntegrityRequirement = htmlentities($risk[0]['CVSS_IntegrityRequirement'], ENT_QUOTES, 'UTF-8', false);
			$AvailabilityRequirement = htmlentities($risk[0]['CVSS_AvailabilityRequirement'], ENT_QUOTES, 'UTF-8',  false);
                	$DREADDamagePotential = htmlentities($risk[0]['DREAD_DamagePotential'], ENT_QUOTES, 'UTF-8', false);
			$DREADReproducibility = htmlentities($risk[0]['DREAD_Reproducibility'], ENT_QUOTES, 'UTF-8', false);
			$DREADExploitability = htmlentities($risk[0]['DREAD_Exploitability'], ENT_QUOTES, 'UTF-8', false);
			$DREADAffectedUsers = htmlentities($risk[0]['DREAD_AffectedUsers'], ENT_QUOTES, 'UTF-8', false);
			$DREADDiscoverability = htmlentities($risk[0]['DREAD_Discoverability'], ENT_QUOTES, 'UTF-8', false);
			$OWASPSkillLevel = htmlentities($risk[0]['OWASP_SkillLevel'], ENT_QUOTES, 'UTF-8', false);
			$OWASPMotive = htmlentities($risk[0]['OWASP_Motive'], ENT_QUOTES, 'UTF-8', false);
			$OWASPOpportunity = htmlentities($risk[0]['OWASP_Opportunity'], ENT_QUOTES, 'UTF-8', false);
			$OWASPSize = htmlentities($risk[0]['OWASP_Size'], ENT_QUOTES, 'UTF-8', false);
			$OWASPEaseOfDiscovery = htmlentities($risk[0]['OWASP_EaseOfDiscovery'], ENT_QUOTES, 'UTF-8', false);
			$OWASPEaseOfExploit = htmlentities($risk[0]['OWASP_EaseOfExploit'], ENT_QUOTES, 'UTF-8', false);
			$OWASPAwareness = htmlentities($risk[0]['OWASP_Awareness'], ENT_QUOTES, 'UTF-8', false);
			$OWASPIntrusionDetection = htmlentities($risk[0]['OWASP_IntrusionDetection'], ENT_QUOTES, 'UTF-8', false);
			$OWASPLossOfConfidentiality = htmlentities($risk[0]['OWASP_LossOfConfidentiality'], ENT_QUOTES, 'UTF-8', false);
			$OWASPLossOfIntegrity = htmlentities($risk[0]['OWASP_LossOfIntegrity'], ENT_QUOTES, 'UTF-8', false);
			$OWASPLossOfAvailability = htmlentities($risk[0]['OWASP_LossOfAvailability'], ENT_QUOTES, 'UTF-8', false);
			$OWASPLossOfAccountability = htmlentities($risk[0]['OWASP_LossOfAccountability'], ENT_QUOTES, 'UTF-8', false);
			$OWASPFinancialDamage = htmlentities($risk[0]['OWASP_FinancialDamage'], ENT_QUOTES, 'UTF-8', false);
			$OWASPReputationDamage = htmlentities($risk[0]['OWASP_ReputationDamage'], ENT_QUOTES, 'UTF-8', false);
			$OWASPNonCompliance = htmlentities($risk[0]['OWASP_NonCompliance'], ENT_QUOTES, 'UTF-8', false);
			$OWASPPrivacyViolation = htmlentities($risk[0]['OWASP_PrivacyViolation'], ENT_QUOTES, 'UTF-8', false);
			$custom = htmlentities($risk[0]['Custom'], ENT_QUOTES, 'UTF-8', false);
		}
		// If the risk was not found use null values
		else
		{
                        $status = "Risk ID Does Not Exist";
                        $subject = "N/A";
                        $reference_id = "N/A";
			$regulation = "";
			$control_number = "N/A";
                        $location = "";
                        $category = "";
                        $team = "";
                        $technology = "";
                        $owner = "";
                        $manager = "";
                        $assessment = "";
                        $notes = "";
                        $submission_date = "";
                        $mitigation_id = "";
                        $mgmt_review = "";
                        $calculated_risk = "0.0";

                        $scoring_method = "";
                        $CLASSIC_likelihood = "";
                        $CLASSIC_impact = "";
                        $AccessVector = "";
                        $AccessComplexity = "";
                        $Authentication = "";
                        $ConfImpact = "";
                        $IntegImpact = "";
                        $AvailImpact = "";
                        $Exploitability = "";
                        $RemediationLevel = "";
                        $ReportConfidence = "";
                        $CollateralDamagePotential = "";
                        $TargetDistribution = "";
                        $ConfidentialityRequirement = "";
                        $IntegrityRequirement = "";
                        $AvailabilityRequirement = "";
		}

		// If the current scoring method was changed to Classic
		if (isset($_GET['scoring_method']) && htmlentities($_GET['scoring_method'], ENT_QUOTES, 'UTF-8', false) == 1)
		{
			// Set the new scoring method
			$scoring_method = change_scoring_method($id, "1");

                        // Audit log
                        $risk_id = $id;
                        $message = "Scoring method was changed for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                        write_log($risk_id, $_SESSION['uid'], $message);

                        $alert = "good";
                        $alert_message = "The scoring method has been successfully changed to Classic.";
		}
                // If the current scoring method was changed to CVSS
                else if (isset($_GET['scoring_method']) && htmlentities($_GET['scoring_method'], ENT_QUOTES, 'UTF-8', false) == 2)
                {
                        // Set the new scoring method
                        $scoring_method = change_scoring_method($id, "2");

                        // Audit log
                        $risk_id = $id;
                        $message = "Scoring method was changed for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                        write_log($risk_id, $_SESSION['uid'], $message);

                        $alert = "good";
                        $alert_message = "The scoring method has been successfully changed to CVSS.";
                }
                // If the current scoring method was changed to DREAD
                else if (isset($_GET['scoring_method']) && htmlentities($_GET['scoring_method'], ENT_QUOTES, 'UTF-8', false) == 3)
                {
                        // Set the new scoring method
                        $scoring_method = change_scoring_method($id, "3");

                        // Audit log
                        $risk_id = $id;
                        $message = "Scoring method was changed for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                        write_log($risk_id, $_SESSION['uid'], $message);

                        $alert = "good";
                        $alert_message = "The scoring method has been successfully changed to DREAD.";
                }
                // If the current scoring method was changed to OWASP
                else if (isset($_GET['scoring_method']) && htmlentities($_GET['scoring_method'], ENT_QUOTES, 'UTF-8', false) == 4)
                {
                        // Set the new scoring method
                        $scoring_method = change_scoring_method($id, "4");

                        // Audit log
                        $risk_id = $id;
                        $message = "Scoring method was changed for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                        write_log($risk_id, $_SESSION['uid'], $message);

                        $alert = "good";
                        $alert_message = "The scoring method has been successfully changed to OWASP.";
                }
                // If the current scoring method was changed to Custom
                else if (isset($_GET['scoring_method']) && htmlentities($_GET['scoring_method'], ENT_QUOTES, 'UTF-8', false) == 5)
                {
                        // Set the new scoring method
                        $scoring_method = change_scoring_method($id, "5");

                        // Audit log
                        $risk_id = $id;
                        $message = "Scoring method was changed for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                        write_log($risk_id, $_SESSION['uid'], $message);

                        $alert = "good";
                        $alert_message = "The scoring method has been successfully changed to Custom.";
                }

                if ($submission_date == "")
                {
                        $submission_date = "N/A";
                }
                else $submission_date = date(DATETIME, strtotime($submission_date));

		// Get the mitigation for the risk
		$mitigation = get_mitigation_by_id($id);

		// If no mitigation exists for this risk
		if ($mitigation == false)
		{
			// Set the values to empty
			$mitigation_date = "N/A";
			$mitigation_date = "";
			$planning_strategy = "";
			$mitigation_effort = "";
			$current_solution = "";
			$security_requirements = "";
			$security_recommendations = "";
		}
		// If a mitigation exists
		else
		{
			// Set the mitigation values
			$mitigation_date = htmlentities($mitigation[0]['submission_date'], ENT_QUOTES, 'UTF-8', false);
			$mitigation_date = date(DATETIME, strtotime($mitigation_date));
			$planning_strategy = htmlentities($mitigation[0]['planning_strategy'], ENT_QUOTES, 'UTF-8', false);
			$mitigation_effort = htmlentities($mitigation[0]['mitigation_effort'], ENT_QUOTES, 'UTF-8', false);
			$current_solution = $mitigation[0]['current_solution'];
			$security_requirements = $mitigation[0]['security_requirements'];
			$security_recommendations = $mitigation[0]['security_recommendations'];
		}

		// Get the management reviews for the risk
		$mgmt_reviews = get_review_by_id($id);

                // If no mitigation exists for this risk
                if ($mgmt_reviews == false)
                {
                        // Set the values to empty
			$review_date = "N/A";
			$review = "";
			$next_step = "";
			$reviewer = "";
			$comments = "";
                }
                // If a mitigation exists
                else
                {
                        // Set the mitigation values
			$review_date = htmlentities($mgmt_reviews[0]['submission_date'], ENT_QUOTES, 'UTF-8', false);
			$review_date = date(DATETIME, strtotime($review_date));
			$review = htmlentities($mgmt_reviews[0]['review'], ENT_QUOTES, 'UTF-8', false);
			$next_step = htmlentities($mgmt_reviews[0]['next_step'], ENT_QUOTES, 'UTF-8', false);
			$next_review = htmlentities(next_review($color, $id, $next_review, false), ENT_QUOTES, 'UTF-8', false);
			$reviewer = htmlentities($mgmt_reviews[0]['reviewer'], ENT_QUOTES, 'UTF-8', false);
			$comments = $mgmt_reviews[0]['comments'];
		}
        }

	// If the risk details were updated
        if (isset($_POST['update_details']))
        {
		// If the user has permission to modify risks
		if (isset($_SESSION["modify_risks"]) && $_SESSION["modify_risks"] == 1)
		{
                	$subject = addslashes($_POST['subject']);
			$reference_id = addslashes($_POST['reference_id']);
			$regulation = (int)$_POST['regulation'];
			$control_number = addslashes($_POST['control_number']);
			$location = (int)$_POST['location'];
                	$category = (int)$_POST['category'];
                	$team = (int)$_POST['team'];
                	$technology = (int)$_POST['technology'];
                	$owner = (int)$_POST['owner'];
                	$manager = (int)$_POST['manager'];
                	$assessment = addslashes($_POST['assessment']);
                	$notes = addslashes($_POST['notes']);

			// Update risk
			update_risk($id, $subject, $reference_id, $regulation, $control_number, $location, $category, $team, $technology, $owner, $manager, $assessment, $notes);

                	// Audit log
                	$risk_id = $id;
                	$message = "Risk details were updated for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                	write_log($risk_id, $_SESSION['uid'], $message);

			$alert = "good";
			$alert_message = "The risk has been successfully modified.";
		}
		// Otherwise, the user did not have permission to modify risks
		else
		{
			$alert = "bad";
                	$alert_message = "You do not have permission to modify risks.  Your attempt to modify the details of this risk was not recorded.  Please contact an Administrator if you feel that you have reached this message in error.";
		}
        }

	// If the user has selected to edit the risk details and does not have permission
	if ((isset($_POST['edit_details'])) && ($_SESSION['modify_risks'] != 1))
	{
        	$alert = "bad";
                $alert_message = "You do not have permission to modify risks.  Any risks that you attempt to modify will not be recorded.  Please contact an Administrator if you feel that you have reached this message in error.";
	}

	// Check if a mitigation was updated
	if (isset($_POST['update_mitigation']))
	{
                $planning_strategy = (int)$_POST['planning_strategy'];
		$mitigation_effort = (int)$_POST['mitigation_effort'];
                $current_solution = addslashes($_POST['current_solution']);
                $security_requirements = addslashes($_POST['security_requirements']);
                $security_recommendations = addslashes($_POST['security_recommendations']);

		// If we don't yet have a mitigation
		if ($mitigation_id == 0)
		{
	                $status = "Mitigation Planned";

                	// Submit mitigation and get the mitigation date back
                	$mitigation_date = submit_mitigation($id, $status, $planning_strategy, $mitigation_effort, $current_solution, $security_requirements, $security_recommendations);
			$mitigation_date = date(DATETIME, strtotime($mitigation_date));
		}
		else
		{
			// Update mitigation and get the mitigation date back
			$mitigation_date = update_mitigation($id, $planning_strategy, $mitigation_effort, $current_solution, $security_requirements, $security_recommendations);
			$mitigation_date = date(DATETIME, strtotime($mitigation_date));
		}

                // Audit log
                $risk_id = $id;
                $message = "Risk mitigation details were updated for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                write_log($risk_id, $_SESSION['uid'], $message);

		$alert = "good";
		$alert_message = "The risk mitigation has been successfully modified.";
	}

	// If comment is passed via GET
	if (isset($_GET['comment']))
	{
		// If it's true
		if ($_GET['comment'] == true)
		{
			$alert = "good";
			$alert_message = "Your comment has been successfully added to the risk.";
		}
	}

        // If closed is passed via GET
        if (isset($_GET['closed']))
        {       
                // If it's true
                if ($_GET['closed'] == true)
                {
                        $alert = "good";
                        $alert_message = "Your risk has now been marked as closed.";
                }
        }

        // If reopened is passed via GET
        if (isset($_GET['reopened']))
        {       
                // If it's true
                if ($_GET['reopened'] == true)
                {       
                        $alert = "good"; 
                        $alert_message = "Your risk has now been reopened.";      
                }
        }
?>

<!doctype html>
<html>
  
  <head>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script language="javascript" src="../js/basescript.js" type="text/javascript"></script>
    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/bootstrap-responsive.css"> 
    <link rel="stylesheet" href="../css/divshot-util.css">
    <link rel="stylesheet" href="../css/divshot-canvas.css">
    <link rel="stylesheet" href="../css/display.css">
    <script type="text/javascript">
      function showScoreDetails() {
        document.getElementById("scoredetails").style.display = "";
        document.getElementById("hide").style.display = "";
        document.getElementById("show").style.display = "none";
      }

      function hideScoreDetails() {
        document.getElementById("scoredetails").style.display = "none";
        document.getElementById("updatescore").style.display = "none";
        document.getElementById("hide").style.display = "none";
        document.getElementById("show").style.display = "";
      }

      function updateScore() {
        document.getElementById("scoredetails").style.display = "none";
	document.getElementById("updatescore").style.display = "";
        document.getElementById("show").style.display = "none";
      }
    </script>
  </head>
  
  <body>
    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/bootstrap-responsive.css">
    <link rel="stylesheet" href="../css/divshot-util.css">
    <link rel="stylesheet" href="../css/divshot-canvas.css">
    <div class="navbar">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="http://www.simplerisk.org/">SimpleRisk</a>
          <div class="navbar-content">
            <ul class="nav">
              <li>
                <a href="../index.php"><?php echo $lang['Home']; ?></a> 
              </li>
              <li class="active">
                <a href="index.php"><?php echo $lang['RiskManagement']; ?></a> 
              </li>
              <li>
                <a href="../reports/index.php"><?php echo $lang['Reporting']; ?></a> 
              </li>
<?php
if (isset($_SESSION["admin"]) && $_SESSION["admin"] == "1")
{
          echo "<li>\n";
          echo "<a href=\"../admin/index.php\">". $lang['Configure'] ."</a>\n";
          echo "</li>\n";
}
          echo "</ul>\n";
          echo "</div>\n";

if (isset($_SESSION["access"]) && $_SESSION["access"] == "granted")
{
          echo "<div class=\"btn-group pull-right\">\n";
          echo "<a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\">".$_SESSION['name']."<span class=\"caret\"></span></a>\n";
          echo "<ul class=\"dropdown-menu\">\n";
          echo "<li>\n";
          echo "<a href=\"../account/profile.php\">". $lang['MyProfile'] ."</a>\n";
          echo "</li>\n";
          echo "<li>\n";
          echo "<a href=\"../logout.php\">". $lang['Logout'] ."</a>\n";
          echo "</li>\n";
          echo "</ul>\n";
          echo "</div>\n";
}
?>
        </div>
      </div>
    </div>
<?php
	if ($alert == "good")
	{
    		echo "<div id=\"alert\" class=\"container-fluid\">\n";
      		echo "<div class=\"row-fluid\">\n";
       		echo "<div class=\"span12 greenalert\">" . $alert_message . "</div>\n";
      		echo "</div>\n";
    		echo "</div>\n";
		echo "<br />\n";
	}
	else if ($alert == "bad")
	{
        	echo "<div id=\"alert\" class=\"container-fluid\">\n";
                echo "<div class=\"row-fluid\">\n";
                echo "<div class=\"span12 redalert\">" . $alert_message . "</div>\n";
                echo "</div>\n";
                echo "</div>\n";
                echo "<br />\n";
        }
?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <ul class="nav  nav-pills nav-stacked">
            <li>
              <a href="index.php">I. <?php echo $lang['SubmitYourRisks']; ?></a> 
            </li>
            <li>
              <a href="index.php?page=plan_mitigations">II. <?php echo $lang['PlanYourMitigations']; ?></a> 
            </li>
            <li >
              <a href="index.php?page=management_review">III. <?php echo $lang['PerformManagementReviews']; ?></a> 
            </li>
            <li>
              <a href="index.php?page=prioritize_planning">IV. <?php echo $lang['PrioritizeForProjectPlanning']; ?></a> 
            </li>
            <li class="active">
              <a href="index.php?page=review_risks">V. <?php echo $lang['ReviewRisksRegularly']; ?></a> 
            </li>
          </ul>
        </div>
        <div class="span9">
          <div class="row-fluid">
            <div class="well">
              <?php view_top_table($id, $calculated_risk, $subject, $status, true); ?>
            </div>
          </div>
          <div class="row-fluid">
            <div id="scoredetails" class="row-fluid" style="display: none;">
              <div class="well">
                  <?php
			// Scoring method is Classic
			if ($scoring_method == "1")
			{
				classic_scoring_table($id, $calculated_risk, $CLASSIC_likelihood, $CLASSIC_impact);
			}
			// Scoring method is CVSS
                    	else if ($scoring_method == "2")
                    	{
				cvss_scoring_table($id, $calculated_risk, $AccessVector, $AccessComplexity, $Authentication, $ConfImpact, $IntegImpact, $AvailImpact, $Exploitability, $RemediationLevel, $ReportConfidence, $CollateralDamagePotential, $TargetDistribution, $ConfidentialityRequirement, $IntegrityRequirement, $AvailabilityRequirement);
                    	}
			// Scoring method is DREAD
			else if ($scoring_method == "3")
			{
				dread_scoring_table($id, $calculated_risk, $DREADDamagePotential, $DREADReproducibility, $DREADExploitability, $DREADAffectedUsers, $DREADDiscoverability);
			}
			// Scoring method is OWASP
			else if ($scoring_method == "4")
			{
				owasp_scoring_table($id, $calculated_risk, $OWASPSkillLevel, $OWASPEaseOfDiscovery, $OWASPLossOfConfidentiality, $OWASPFinancialDamage, $OWASPMotive, $OWASPEaseOfExploit, $OWASPLossOfIntegrity, $OWASPReputationDamage, $OWASPOpportunity, $OWASPAwareness, $OWASPLossOfAvailability, $OWASPNonCompliance, $OWASPSize, $OWASPIntrusionDetection, $OWASPLossOfAccountability, $OWASPPrivacyViolation);
			}
			// Scoring method is Custom
			else if ($scoring_method == "5")
			{
				custom_scoring_table($id, $custom);
			}
                  ?>
              </div>
            </div>
            <div id="updatescore" class="row-fluid" style="display: none;">
              <div class="well">
                  <?php
                        // Scoring method is Classic
                        if ($scoring_method == "1")
                        {
				edit_classic_score($CLASSIC_likelihood, $CLASSIC_impact);
                        }
                        // Scoring method is CVSS
                        else if ($scoring_method == "2")
                        {
				edit_cvss_score($AccessVector, $AccessComplexity, $Authentication, $ConfImpact, $IntegImpact, $AvailImpact, $Exploitability, $RemediationLevel, $ReportConfidence, $CollateralDamagePotential, $TargetDistribution, $ConfidentialityRequirement, $IntegrityRequirement, $AvailabilityRequirement);
                        }
                        // Scoring method is DREAD
                        else if ($scoring_method == "3")
                        {
				edit_dread_score($DREADDamagePotential, $DREADReproducibility, $DREADExploitability, $DREADAffectedUsers, $DREADDiscoverability);
                        }
                        // Scoring method is OWASP
                        else if ($scoring_method == "4")
                        {
				edit_owasp_score($OWASPSkillLevel, $OWASPMotive, $OWASPOpportunity, $OWASPSize, $OWASPEaseOfDiscovery, $OWASPEaseOfExploit, $OWASPAwareness, $OWASPIntrusionDetection, $OWASPLossOfConfidentiality, $OWASPLossOfIntegrity, $OWASPLossOfAvailability, $OWASPLossOfAccountability, $OWASPFinancialDamage, $OWASPReputationDamage, $OWASPNonCompliance, $OWASPPrivacyViolation);
                        }
                        // Scoring method is Custom
                        else if ($scoring_method == "5")
                        {
				edit_custom_score($custom);
                        }
                  ?>
              </div>
            </div>
          </div>
          <div class="row-fluid">
            <div class="span4">
              <div class="well">
                <form name="details" method="post" action="">
		<?php
			// If the user has selected to edit the risk
			if (isset($_POST['edit_details']))
			{
				edit_risk_details($submission_date, $subject, $reference_id, $regulation, $control_number, $location, $category, $team, $technology, $owner, $manager, $assessment, $notes, $CLASSIC_likelihood, $CLASSIC_impact, $AccessVector, $AccessComplexity, $Authentication, $ConfImpact, $IntegImpact, $AvailImpact, $Exploitability, $RemediationLevel, $ReportConfidence, $CollateralDamagePotential, $TargetDistribution, $ConfidentialityRequirement, $IntegrityRequirement, $AvailabilityRequirement, $DREADDamagePotential, $DREADReproducibility, $DREADExploitability, $DREADAffectedUsers, $DREADDiscoverability, $OWASPSkillLevel, $OWASPMotive, $OWASPOpportunity, $OWASPSize, $OWASPEaseOfDiscovery, $OWASPEaseOfExploit, $OWASPAwareness, $OWASPIntrusionDetection, $OWASPLossOfConfidentiality, $OWASPLossOfIntegrity, $OWASPLossOfAvailability, $OWASPLossOfAccountability, $OWASPFinancialDamage, $OWASPReputationDamage, $OWASPNonCompliance, $OWASPPrivacyViolation, $custom, $assessment, $notes);
			}
			// Otherwise we are just viewing the risk
			else
			{
				view_risk_details($submission_date, $subject, $reference_id, $regulation, $control_number, $location, $category, $team, $technology, $owner, $manager, $assessment, $notes);
			}
		?>
                </form>
              </div>
            </div>
            <div class="span4">
              <div class="well">
                <form name="mitigation" method="post" action="">
		<?php
			// If the user has selected to edit the mitigation
			if (isset($_POST['edit_mitigation']))
			{ 
				edit_mitigation_details($mitigation_date, $planning_strategy, $mitigation_effort, $current_solution, $security_requirements, $security_recommendations);
			}
			// Otherwise we are just viewing the mitigation
			else
			{
				view_mitigation_details($mitigation_date, $planning_strategy, $mitigation_effort, $current_solution, $security_requirements, $security_recommendations);
			}
		?>
                </form>
              </div>
            </div>
            <div class="span4">
              <div class="well">
                <form name="review" method="post" action="">
		<?php
			view_review_details($id, $review_date, $reviewer, $review, $next_step, $next_review, $comments);
		?>
                </form>
              </div>
            </div>
            </form>
          </div>
          <div class="row-fluid">
            <div class="well">
              <h4><?php echo $lang['Comments']; ?></h4>
              <?php get_comments($id); ?>
            </div>
          </div>
          <div class="row-fluid">
            <div class="well">
              <h4><?php echo $lang['AuditTrail']; ?></h4>
              <?php get_audit_trail($id); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
