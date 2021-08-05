var express = require('express');
var router = express.Router();
const kaltura = require('kaltura-client');
var KalturaClientFactory = require('../lib/kalturaClientFactory');

router.get('/', async function (req, res, next) {
  try {
    res.render('index');
  } catch (e) {
    res.render('error', { message: e, error: e });
  }
});

router.post('/', async function (req, res, next) {
  try {
    /*
      The ID of the video entry whose caption asset you wish to edit. 
      Get from media.list or https://kmc.kaltura.com/index.php/kmcng/content/entries/list
    */
    var entryId = req.body.entryId;
    var assetId = null;

    var privilegesStr = 'sview:' + entryId
      + 'disableentitlementforentry:' + entryId
      + ',appid:' + process.env.KALTURA_APP_NAME + '-' + process.env.KALTURA_APP_DOMAIN;

    var adminks = await KalturaClientFactory.getKS(process.env.KALTURA_USER_ID, 
      { 
        type: kaltura.enums.SessionType.ADMIN,
        privileges:privilegesStr 
      });

    //var client = await KalturaClientFactory.getClient(adminks);

    /* Create the iframe URL to embed the editor. The app expects these params (* is mandatory):
    1. pid* - your partner id
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

    var editorIframeSrcUrl = "https://www.kaltura.com/apps/captionstudio/latest/index.html?"
    + "pid=" + process.env.KALTURA_PARTNER_ID
    + '&entryid=' + entryId
    + (assetId != null ? '&assetid=' + assetId : '')
    + '&serviceurl='+ process.env.KALTURA_SERVICE_URL
    + '&cdnurl=https://cdnapisec.kaltura.com'
    + '&ks=' + adminks;

    res.render('index', { iFrameUrl: editorIframeSrcUrl });

  } catch (e) {
    res.render('error', { message: e, error: e });
  }
});

module.exports = router;
