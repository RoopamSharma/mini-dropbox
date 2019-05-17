<?php
// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');
require_once 'demo-lib.php';
demo_init(); // this just enables nicer output
// if there are many files in your Dropbox it can take some time, so disable the max. execution time
set_time_limit( 0 );
ini_set('display_errors','On');
require_once 'DropboxClient.php';
/** you have to create an app at @see https://www.dropbox.com/developers/apps and enter details below: */
/** @noinspection SpellCheckingInspection */
$dropbox = new DropboxClient( array(
	'app_key' => "bn558ve2recx77w",      // Put your Dropbox API key here
	'app_secret' => "riajzt5dyyy7jdz",   // Put your Dropbox API secret here
	'app_full_access' => false,
) );
/**
 * Dropbox will redirect the user here
 * @var string $return_url
 */
$return_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?auth_redirect=1";
// first, try to load existing access token
$bearer_token = demo_token_load( "bearer" );
if ( $bearer_token ) {
	$dropbox->SetBearerToken( $bearer_token );
	//echo "loaded bearer token: " . json_encode( $bearer_token, JSON_PRETTY_PRINT ) . "\n";
} elseif ( ! empty( $_GET['auth_redirect'] ) ) // are we coming from dropbox's auth page?
{
	// get & store bearer token
	$bearer_token = $dropbox->GetBearerToken( null, $return_url );
	demo_store_token( $bearer_token, "bearer" );
} elseif ( ! $dropbox->IsAuthorized() ) {
	// redirect user to Dropbox auth page
	$auth_url = $dropbox->BuildAuthorizeUrl( $return_url );
	die( "Authentication required. <a href='$auth_url'>Continue.</a>" );
}
if(isset($_FILES["userfiles"])){
	for($i=0;$i<sizeof($_FILES["userfiles"]["name"]);$i++){
		#move_uploaded_file($_FILES["userfiles"]["tmp_name"][$i], "uploads/".$_FILES["userfiles"]["name"][$i]);
		#$dropbox->UploadFile("uploads/".$_FILES["userfiles"]["name"][$i]);
		#unlink("uploads/".$_FILES["userfiles"]["name"][$i]);
		$dropbox->UploadFile($_FILES["userfiles"]["tmp_name"][$i],$_FILES["userfiles"]["name"][$i]);
	}
}
if(isset($_GET["delimg"])){
	$img = $dropbox->Delete($_GET["delimg"]);
	header("Location: album.php");
}
?>
<html>
<head>
<link type="text/css" rel="stylesheet" href="album.css"> 
</head>
<body>
<form enctype="multipart/form-data" action="album.php" method="POST" class="form">
Select the files: <input type="file" name="userfiles[]" accept=".jpg" multiple/><br/>
<input type="submit" id="btnupload" value="Upload"/>
</form>
<script>
function displayImg(img){
	document.getElementById("selectedimg").src = "data:image/jpeg;base64,"+img;
	document.getElementById("selectedimg").visibility = "visible";
}
</script>
<?php
$files = $dropbox->GetFiles( "/", false );
echo "<div class='list'>";
echo "<ul>";
foreach($files as $file){
	$img_data = base64_encode( $dropbox->GetThumbnail( $file->path,'l' ) );
	echo "<li><label class='item' onclick=displayImg('".$img_data."');>";
	echo $file->name;
	echo "</label><a id='btndel' href='album.php?delimg=".$file->path."'>Delete</a></li>";
}
echo "</ul>";
echo "</div>";
?>
<div class="imgclass">
<img visibility="hidden" id='selectedimg'/>
</div>
</body>
</html>