<?php
$partnerId = 'YOUR_KALTURA_ACCOUNT_ID'; //https://kmc.kaltura.com/index.php/kmcng/settings/integrationSettings
$apiAdminSecret = 'YOUR_KALTURA_API_ADMIN_SECRET'; //https://kmc.kaltura.com/index.php/kmcng/settings/integrationSettings
$kalturaServiceUrl = 'https://www.kaltura.com';
//This goes on your backend - never include API secret keys in front end code
$sessionExpire = 60 * 60 * 24; //one day

/* Real UserId so Analytics and Entitlements will be 
tracked according to your business needs */
$uniqueUserId = 'someone@company.com'; 
/*
The ID of the video entry whose caption asset you wish to edit. 
Get from media.list or https://kmc.kaltura.com/index.php/kmcng/content/entries/list
*/
$entryId = 'YOUR_VIDEO_ID'; 

/* the ID of the caption Asset to edit. 
retrieved by captionAsset.list using the entry ID, formatIn="1,2" (SRT or DFXP) and statusEqual=2 (READY). 
If set to null, the first asset in the list response will be loaded by the editor.*/
$assetId = null; 

/* Used to designate your application name, 
this can be used in the Analytics later to differentiate 
usage across different apps (such as website vs. mobile iOS vs. mobile Android vs. partner site) */
$appName = 'myAppName'; 
$appDomain = 'myapp.com'; // the domain to track this playback session to

//generate the Kaltura Session for secure and tracked playback session

/*'YourCategoryName'; if your entries are inside a category 
with a defined privacyContext, this must be specified too */

$privacyContext = null; 
$privilegesStr = 'sview:' . $entryId 
. 'disableentitlementforentry:' . $entryId 
. ',appid:' . $appName . '-' 
. $appDomain . ($privacyContext != null ? ',privacycontext:' . $privacyContext : '');

$sessionStartRESTAPIUrl = $kalturaServiceUrl . '/api_v3/service/session/action/start/format/1/'
.'secret/'. $apiAdminSecret 
. '/partnerId/' . $partnerId 
. '/type/2/expiry/' . $sessionExpire 
. '/userId/' . $uniqueUserId 
. '/privileges/' . $privilegesStr;

$ks = json_decode(file_get_contents($sessionStartRESTAPIUrl));

/* Create the iframe URL to embed the editor. The app expects these params (* is mandatory):
1. pid* - your partenr id
2. ks* - KS with privileges to update captions (caption_captionasset --> setContent)
3. entryid* - the entry that hosts the caption(s)
4. assetid - the initial captions language asset id. If provided, 
	the editor will try to open this specific captions asset as the 1st loaded asset to edit. 
	All other captions assets of the given entry will still be available to edit.
5. maxcharsperline - maximum chars allowed per caption line. If not defined - no char count limit.
6. cdnurl - Path for the player CDN. If not set the default will be 
	production cdn URL: http://cdnapi.kaltura.com. 
	Recommended to explicitly set this to https://cdnapisec.kaltura.com in order to force SSL.
7. serviceurl - API URL. If you need to work with self hosted Kaltura environment. 
	If not set default will be http://www.kaltura.com. 
	Recommended to explicitly set this to https://www.kaltura.com in order to force SSL.
*/

$editorIframeSrcUrl = 'https://www.kaltura.com/apps/captionstudio/latest/index.html?pid=' . $partnerId 
. '&entryid=' . $entryId 
. ($assetId != null ? '&assetid=' . $assetId : '') 
. '&serviceurl=' . $kalturaServiceUrl 
. '&cdnurl=https://cdnapisec.kaltura.com&' 
. '&ks=' . $ks;

?>
<!DOCTYPE html>
<html>

<head>
	<style type="text/css">
		.kccs-frame {
			width: 100%;
			margin-top: 20px;
			min-height: 672px;
			margin-left: -15px;
			margin-right: -15px;
			border-width: 2px;
			border-style: inset;
			border-color: initial;
			border-image: initial;
		}
	</style>

</head>

<body>
	<h1>Important Notes:</h1>
	<ul>
		<li>The captions editor app is designed to run on desktops and tablets/ipads on landscape mode only.</li>
		<li>The app player and API protocol is http. If we want https we need to explicitly send both cdnurl and serviceurl attributes with corresponding parameters</li>
	</ul>
	<iframe id="kalturaCaptionsEditor" class="kccs-frame" src="<?php echo $editorIframeSrcUrl; ?>"></iframe>
</body>

</html>