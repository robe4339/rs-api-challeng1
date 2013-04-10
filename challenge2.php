<?php
// (c)2012 Rackspace Hosting
// See COPYING for licensing information
// 
// Challenge 2
// Write a script that clones a server (takes an image and deploys the image as a new server)

namespace OpenCloud;

require_once('rackspace.php');
require_once('compute.php');

/**
 * numbers each step
 */
function step($msg,$p1=NULL,$p2=NULL,$p3=NULL) {
    global $STEPCOUNTER;
    printf("\nStep %d. %s\n", ++$STEPCOUNTER, sprintf($msg,$p1,$p2,$p3));
}


// Callback function for WaitFor
function progress($server) {
    printf("%s:%-8s %3d%% complete\r",
        $server->name, $server->status, $server->progress);
}

define('TIMEFORMAT', 'r');

$inifile = $_SERVER['HOME'] . "/.rackspace_cloud_credentials";
define('INIFILE', $inifile);
$ini = parse_ini_file(INIFILE, TRUE);
if (!$ini) {
    printf("Unable to load .ini file [%s]\n", INIFILE);
    exit;
}


// establish our credentials
step('Authenticate');
$connection = new Rackspace(
		$ini['Identity']['url'],
                array( 'username' => $ini['Identity']['username'],
		       'tenantName' => $ini['Identity']['tenant'],
		       'apiKey' => $ini['Identity']['apiKey']
		 ));

step('Connect to Cloud Servers');
// now, connect to the compute service
$compute = $connection->Compute($ini['Compute']['serviceName'],$ini['Compute']['region']);

// list ALL the images
step("All images avalable:\n");
$imlist = $compute->ImageList(TRUE, array('type'=>'SNAPSHOT'));

if(!$imlist){echo "No Images Available";}else{
while($image = $imlist->Next()) {
    printf("\tID = %s - Name = %s\n", $image->id, $image->name);
}
}

fwrite(STDOUT, "Please choose an image to use\n");

$varin = fgets(STDIN);

fwrite(STDOUT,"You choose $varin\n");


// list the flavors
print("Flavors:\n");
$flist = $compute->FlavorList();
while($flavor = $flist->Next()) {
    printf("\t%s - %s\n", $flavor->id, $flavor->name);
}

fwrite(STDOUT, "Please choose a flavor to use\n");

$flavor = fgets(STDIN);

fwrite(STDOUT,"You choose $flavor\n");


fwrite(STDOUT, "Please choose a name for your new server\n");

$servername = fgets(STDIN);

fwrite(STDOUT,"You Choose $servername");

define('IMAGE_ID', $varin);
define('FLAVOR_ID', $flavor);

$server = $compute->Server();
step("Building Server");
$server->Create(array(
        'name' => $servername,
        'image' => $compute->Image(IMAGE_ID),
        'flavor' => $compute->Flavor(FLAVOR_ID)));

printf("The root password is [%s]\n", $server->adminPass);

step("Wait for it to finish...\n");
$server->WaitFor("ACTIVE", 600, 'OpenCloud\progress');

print("done\n");
exit(0);





