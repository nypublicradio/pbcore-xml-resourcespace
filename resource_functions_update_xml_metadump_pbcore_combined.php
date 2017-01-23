<?php
# Resource functions
# Functions to create, edit and index resources

include_once __DIR__ . '/definitions.php';		// includes log code definitions for resource_log() callers.
	
function update_xml_metadump_pbcore($resource)
	{
	# Updates the XML metadata dump file when the resource has been altered.
	global $xml_metadump,$xml_metadump_pbcore_map;
	if (!$xml_metadump || $resource < 0) {return true;} # Only execute when configured and when not a template
	
	$path=dirname(get_resource_path($resource,true,"pre",true)) . "/metadumpCombined.xml";
	hook("before_update_xml_metadump");
	if (file_exists($path)){$wait=unlink($path);}

	$f=fopen($path,"w");
	fwrite($f,"<?xml version=\"1.0\"?>\n");
	fwrite($f,"<pbcoreDescriptionDocument xmlns=\"http://www.pbcore.org/PBCore/PBCoreNamespace.html\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.pbcore.org/PBCore/PBCoreNamespace.html https://raw.githubusercontent.com/WGBH/PBCore_2.1/master/pbcore-2.1.xsd\" >\n\n");

  	$data=get_resource_field_data($resource,false,false); # Get field data ignoring permissions
	$dateType=$data[17];
	$IdentifierSource2=$data[5];
	$proxyFilename=$data[15];
	$preservationFilename=$data[32];
	$physicalFormat=$data[18];
    
# asset info
  	for ($n=0;$n<count($data);$n++)
	  	{
	  	# Value processing
	  	$value=$data[$n]["value"];
	  	if (substr($value,0,1)==",") {$value=substr($value,1);} # Checkbox lists / dropdowns; remove initial comma
	  	$value=trim($value," ,\t\n\r\0\x0B");

		#write beginning
		if ($value !='')
			{
			if ($data[$n]["name"]=="uniqueID" || $data[$n]["name"]=="IdentifierSource2" || $data[$n]["name"]=="Identifier2" || $data[$n]["name"]=="itemTitle" || $data[$n]["name"]=="assetType" || $data[$n]["name"]=="subject" || $data[$n]["name"]=="description" || $data[$n]["name"]=="epsID" || $data[$n]["name"]=="programName" || $data[$n]["name"]=="contributor" || $data[$n]["name"]=="rightsStatement" || $data[$n]["name"]=="publisher" || $data[$n]["name"]=="ancillaryMaterials" || $data[$n]["name"]=="
			Coverage" || $data[$n]["name"]=="temporalCoverage" || $data[$n]["name"]=="relatedIDs")
				{
				if ($data[$n]["name"]=="uniqueID")
					{
					# pbcorePublisher
					fwrite($f,"<pbcoreIdentifier source=\"KBOO\">kboo_" . htmlspecialchars($value) . "</pbcoreIdentifier>\n");
					}
				if ($data[$n]["name"]=="Identifier2")
					{
					fwrite($f,"<pbcoreIdentifier source=\"" . $IdentifierSource2["value"] . "\">" . htmlspecialchars($value) . "</pbcoreIdentifier>\n");
					}
				elseif ($data[$n]["name"]=="publisher")
					{
					# pbcorePublisher
					fwrite($f,"<pbcorePublisher>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">" .htmlspecialchars($value). "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n</pbcorePublisher>\n");
					}
				elseif ($data[$n]["name"]=="relatedIDs")
					{
					# pbcoreRelation relatedIDs
					fwrite($f,"<pbcoreRelation>\n\t<pbcoreRelationType>References</pbcoreRelationType>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]]. ">");
					fwrite($f,htmlspecialchars($value). "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n</pbcoreRelation>\n");
					}
				elseif ($data[$n]["name"]=="epsID")
					{
					# pbcoreRelation epsID
					fwrite($f,"<pbcoreRelation>\n\t<pbcoreRelationType>References</pbcoreRelationType>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]]. ">");
					fwrite($f,htmlspecialchars($value). "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n</pbcoreRelation>\n");
					}
				elseif ($data[$n]["name"]=="programName")
					{
					# pbcoreRelation epsID
					fwrite($f,"<pbcoreRelation>\n\t<pbcoreRelationType>Is Part Of</pbcoreRelationType>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]]. ">");
					fwrite($f,htmlspecialchars($value). "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n</pbcoreRelation>\n");
					}
				elseif ($data[$n]["name"]=="subject")
					{
					# subjects
					$subjectArray = preg_split("/[,]+/", $value);
					foreach ($subjectArray as $subject_value)
						{
						$subject_value = trim($subject_value," ");
						fwrite($f,"<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " subjectType=\"keyword\" subjectTypeSource=\"KBOO topic terms\">" . htmlspecialchars($subject_value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}
					}
				elseif ($data[$n]["name"]=="contributor")
					{
					# pbcorePublisher
					$contributorsArray = preg_split("/[,]+/", $value);
					foreach ($contributorsArray as $contributor_value)
						{
						$contributor_name = strtok($contributor_value,"(");
						$contributor_name=trim($contributor_name," ");
						$contributor_role = strrchr($contributor_value,"(");
						if ($contributor_role != '')
							{
							$contributor_role=trim($contributor_role," ()");
							fwrite($f,"<pbcoreContributor>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">" . htmlspecialchars($contributor_name) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n\t<contributorRole>" . htmlspecialchars($contributor_role) . "</contributorRole>\n</pbcoreContributor>\n");
							}
						else
							{
						
							fwrite($f,"<pbcoreContributor>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">" . htmlspecialchars($contributor_name) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n</pbcoreContributor>\n");
							}
						}
					}
				elseif ($data[$n]["name"]=="ancillaryMaterials")
					{
					# ancillaryMaterials
					fwrite($f,"<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " annotationType=\"Ancillary Materials\">".htmlspecialchars($value). "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				elseif ($data[$n]["name"]=="spatialCoverage")
					{
					# spatial coverage
					$spatialArray = preg_split("/[,]+/", $value);
					foreach ($spatialArray as $spatial_value)
						{
						$spatial_value = trim($spatial_value," ");
						fwrite($f,"<pbcoreCoverage>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " source=\"local\">" . htmlspecialchars($spatial_value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n\t<coverageType>spatial</coverageType>\n</pbcoreCoverage>\n");
						}
					}
				elseif ($data[$n]["name"]=="temporalCoverage")
					{
					# temporal coverage
					fwrite($f,"<pbcoreCoverage>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]].">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n\t<coverageType>temporal</coverageType>\n</pbcoreCoverage>\n");
					}
				elseif ($data[$n]["name"]=="rightsStatement")
					{
					# temporal coverage
					fwrite($f,"<pbcoreRightsSummary>\n\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]].">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n</pbcoreRightsSummary>\n");
					}
				elseif ($data[$n]["name"]=="itemTitle" || $data[$n]["name"]=="assetType" || $data[$n]["name"]=="description")
					{
					# additional PBCore fields
					fwrite($f,"<" . $xml_metadump_pbcore_map[$data[$n]["name"]].">");
					fwrite($f,htmlspecialchars($value));
					fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				}
			}
		elseif ($data[$n]["name"]=="description")
			{
			fwrite($f,"<" . $xml_metadump_pbcore_map[$data[$n]["name"]]."/>\n");
			}
	  	}

# proxy file instantiation
	if ($proxyFilename["value"] !='')
		{
		fwrite($f,"<pbcoreInstantiation>\n");
		for ($n=0;$n<count($data);$n++)
			{
			# Value processing
			$value=$data[$n]["value"];
			if (substr($value,0,1)==",") {$value=substr($value,1);} # Checkbox lists / dropdowns; remove initial comma
			$value=trim($value," ,\t\n\r\0\x0B");

			#write beginning
			if ($value !=''){
			
				if ($data[$n]["name"]=="proxyFilename")
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " source=\"KBOO\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					fwrite($f,"\t<instantiationDigital>audio/mpeg3</instantiationDigital>\n");
					fwrite($f,"\t<instantiationLocation>A:/_final</instantiationLocation>\n");
					fwrite($f,"\t<instantiationMediaType>Sound</instantiationMediaType>\n");
					fwrite($f,"\t<instantiationGenerations>Copy</instantiationGenerations>\n");
					}
				elseif ($data[$n]["name"]=="durationDigital")
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				}
			}
		fwrite($f,"</pbcoreInstantiation>\n");
		}

# preservation file instantiation
	if ($preservationFilename["value"] !='')
		{	
		fwrite($f,"<pbcoreInstantiation>\n");
		for ($n=0;$n<count($data);$n++)
			{
			# Value processing
			$value=$data[$n]["value"];
			if (substr($value,0,1)==",") {$value=substr($value,1);} # Checkbox lists / dropdowns; remove initial comma
			$value=trim($value," ,\t\n\r\0\x0B");

			if ($value !='')
				{
				if ($data[$n]["name"]=="dateDigitized" || $data[$n]["name"]=="preservationFilename" || $data[$n]["name"]=="checksumValue" || $data[$n]["name"]=="fileLocation" || $data[$n]["name"]=="filesize" || $data[$n]["name"]=="channelConfigDigital" || $data[$n]["name"]=="sampleRate" || $data[$n]["name"]=="bitDepth" || $data[$n]["name"]=="durationDigital" || $data[$n]["name"]=="audioTimeStart" || $data[$n]["name"]=="checksumValue" || $data[$n]["name"]=="digitalQC")
					{
					if ($data[$n]["name"]=="checksumValue")
						{
						fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " source=\"md5\">");
						fwrite($f,htmlspecialchars($value));
						fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}
					elseif ($data[$n]["name"]=="filesize")
						{
						fwrite($f,"\t<instantiationMediaType>Sound</instantiationMediaType>\n");
						fwrite($f,"\t<instantiationGenerations>Master: preservation</instantiationGenerations>\n");
						$filesizeArray = preg_split("/\s+/", $value);
						fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]]. " unitsOfMeasure=\"" . $filesizeArray[1] . "\">");
						fwrite($f,htmlspecialchars($filesizeArray[0]));
						fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}				
					elseif ($data[$n]["name"]=="sampleRate")
						{
						fwrite($f,"\t<instantiationEssenceTrack>\n\t\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " unitsOfMeasure=\"kHz\">");
						fwrite($f,trim(htmlspecialchars($value),", kHz"));
						fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}
					elseif ($data[$n]["name"]=="bitDepth")
						{
						fwrite($f,"\t\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]]. ">");
						fwrite($f,trim(htmlspecialchars($value),", bits"));
						fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">");
						fwrite($f,"\n\t</instantiationEssenceTrack>\n");
						}
					elseif ($data[$n]["name"]=="digitalQC")
						{
						fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]]. " annotationType=\"digital QC notes\">");
						fwrite($f,htmlspecialchars($value));
						fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}
					elseif ($data[$n]["name"]=="preservationFilename")
						{
						fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " source=\"filename\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}
					elseif ($data[$n]["name"]=="dateDigitized")
						{
						fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " dateType=\"created\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						fwrite($f,"\t<instantiationDigital>audio/x-wav</instantiationDigital>\n");					
						}
					else
						{
						fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
						}
					}
				}
			}
		fwrite($f,"</pbcoreInstantiation>\n");
		}

# physical instantiation
	if ($physicalFormat["value"] !='')
		{
		fwrite($f,"<pbcoreInstantiation>\n");
		for ($n=0;$n<count($data);$n++)
			{
			# Value processing
			$value=$data[$n]["value"];
			if (substr($value,0,1)==",") {$value=substr($value,1);} # Checkbox lists / dropdowns; remove initial comma
			$value=trim($value," ,\t\n\r\0\x0B");

			#write beginning
			if ($value !=''){
				if ($data[$n]["name"]=="uniqueID" || $data[$n]["name"]=="date" || $data[$n]["name"]=="storageLocation" || $data[$n]["name"]=="physicalFormat" || $data[$n]["name"]=="generations" || $data[$n]["name"]=="condition" || $data[$n]["name"]=="notesOnCasing" || $data[$n]["name"]=="durationPhysical" || $data[$n]["name"]=="channelConfigPhysical") 
				{
				if ($data[$n]["name"]=="uniqueID")
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " source=\"KBOO\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				elseif ($data[$n]["name"]=="date")
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " dateType=\"" . trim($dateType["value"],",") . "\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				elseif ($data[$n]["name"]=="notesOnCasing")
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " annotationType=\"written notes on casing\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				elseif ($data[$n]["name"]=="condition")
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . " annotationType=\"physical condition\">" . htmlspecialchars($value) . "</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				else
					{
					fwrite($f,"\t<" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">");
					fwrite($f,htmlspecialchars($value));
					fwrite($f,"</" . $xml_metadump_pbcore_map[$data[$n]["name"]] . ">\n");
					}
				}
			}
		
			}
		fwrite($f,"</pbcoreInstantiation>\n");
		}

	fwrite($f,"</pbcoreDescriptionDocument>\n");
	fclose($f);
	hook("after_update_xml_metadump");
	//chmod($path,0777); // fixme - temporarily make world readable/writable until we have better solution for file permissions
	}