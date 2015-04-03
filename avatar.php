<?php
$pageTitle = "Avatars";
include_once('includes/session.php');
include_once('includes/functions_avatars.php');

// are we logged in? no → leave
if ( !login_check() ) {
	header( "Location: /" );
	exit();
} else {
	$me = $_SESSION['u'];
}

$action = "Failure";
$body = "";
$style = " panel-success";

// are we supplying query for name + key? → test validity
//	download image and store, write to log, remove secret file
//  or → leave
if ( isset( $_GET['name'] ) and isset( $_GET['key'] ) ) {

	$action = "Users Smell (They Should Never See This)";
	$requestedName = sanitiseName( $_GET['name'] );
	$requestedKey =  sanitiseKey( $_GET['key'] );
	$requestPermission = $avatarKeyPath . '/' . $requestedName;
	$continue = true;

	if ( file_exists( $requestPermission ) and !is_dir( $requestPermission ) ) {
		$permission = file_get_contents( $requestPermission );
		list($givenKey, $givenAdmin, $givenTime) = explode( ':', $permission, 3 );
	} else { $continue = false; }

	if ( $continue and ( $givenKey === $requestedKey ) ) {
		$requestedPath = $avatarFilePath . '/' . $requestedName . '.png';
		$requestedURL  = $_SESSION['a']; // we trust Valve gave us unshit URL
		$result = storeURL( $requestedURL, $requestedPath, false );
	} else { $continue = false; }

	if ( $continue and $result ) {
		writeAvatarLog( $me, $givenAdmin, $requestedName, 'add' );
		// unlink( $requestPermission );
		header( "Location: /avatars/" . $requestedName . '.png' );
		exit();
	} else {
		header( "Location: /" );
		exit();
	}
}

// are we admin? no → leave
if ( in_array( $me, getAdmins() ) ) {
} else {
	header( "Location: /" );
	exit();
}


// are we supplying data via POST? → write to log, save image data to name
if ( isset( $_POST['name'] ) and isset( $_FILES['userfile'] ) ) {

	$action = "Upload File";
	$requestedName = sanitiseName( $_POST['name'] );
	$requestedPath = $avatarFilePath . '/' . $requestedName . '.png';
	$hostedURL		= '/avatars/' . $requestedName . '.png';

	// do we want to be able to overwrite?
	if ( !file_exists( $requestedPath ) and !is_dir( $requestedPath ) ) {

		if ( is_uploaded_file( $_FILES['userfile']['tmp_name'] ) and ( $_FILES['userfile']['size'] < 500000 ) ) {

			$image = getimagesize( $_FILES['userfile']['tmp_name'] );
			// NOTE we have a more limited set of images to accept here. No BMP ffs!
			$typesAllowed = array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG );

			if ( ( $image !== false and in_array( $image[2],  $typesAllowed ) ) ) {

				if ( move_uploaded_file($_FILES['userfile']['tmp_name'], $requestedPath ) ) {

					// success
					writeAvatarLog( 0, $me, $requestedName, 'upload' );
					$body = "<p>File uploaded. [<img height=\"14\" width=\"14\" src=\"{$hostedURL}\" />]</p>";
				} else {

					$style = "panel-danger";
					$body = "<p>File failed to move to location.</p>";
				}
			} else {

				$style = "panel-danger";
				$body = "<p>File failed to validate as an image.</p>";
			}
		} else {

			$style = "panel-danger";
			switch($_FILES['userfile']['error']){
				case 0:
					$body = "<p>There was a problem with your upload.</p>";
					break;
				case 1:
				case 2:
					$body = "<p>The file is too big.</p>";
					break;
				case 3:
					$body = "<p>File upload was halted, resubmit.</p>";
					break;
				case 4:
					$body = "<p>No file selected? Wake up Cheese :)</p>";
					break;
				default:
					$body = "<p>File failed to upload.</p>";
			}
		}
	} else {

		// Either trying to overwrite a file or /
		$style = "panel-danger";
		$body = "<p>The choosen handle already exists, or is invalid.</p>";
	}
}


// are we supplying query for grant + name? → write to log, write out secret file
// also supply a URL you can give to someone in private that contains name+key
if ( isset( $_GET['grant'] ) and isset( $_GET['name'] ) ) {

	$action = "Grant Avatar Permission";
	$grantedName = sanitiseName( $_GET['name'] );
	$grantedKey =  md5(uniqid(mt_rand(), true));
	$requestPermission = $avatarKeyPath . '/' . $grantedName;

	// do we want to be able to overwrite?
	if ( !file_exists( $requestPermission ) and !is_dir( $requestPermission ) ) {

		$permissionSlip = $grantedKey . ':' . $me . ':' . time();
		writeAvatarLog( 0, $me, $grantedName, 'granting' );
		file_put_contents( $requestPermission, $permissionSlip );
		// TODO: take current URI, clean, add query
		$theirURL = "/avatar/?name=" . $grantedName . "&amp;key=" . $grantedKey;
		$body = "<p>Permission has been granted for {$grantedName}, you may give them <a href=\"{$theirURL}\">this link</a>.</p>";
	} else {
		/* we ought to probably read the file and reshare link here */
		$style = "panel-danger";
		// TODO: take current URI, clean, add query
		$revokeURL = "/avatar/?name=" . $grantedName . "&amp;revoke=please";
		$body = "<p>This user already has permission, do you want to <a href=\"{$revokeURL}\">revoke it</a> and try again?</p>";
	}

}

// are we supplying query for name + email? → write to log, pull down gravatar image
if ( isset( $_GET['email'] ) and isset( $_GET['name'] ) ) {

	$action = "Gravatar Upload";
	$requestedName = sanitiseName( $_GET['name'] );
	// http://en.gravatar.com/site/implement/hash/
	$gravatar		= md5( strtolower( trim( $_GET['email'] ) ) );
	$requestedURL	= "http://www.gravatar.com/avatar/" . $gravatar;
	$hostedURL		= '/avatars/' . $requestedName . '.png';

	$requestedPath = $avatarFilePath . '/' . $requestedName . '.png';
	/* this returns false for existing file */
	$result = storeURL( $requestedURL, $requestedPath, false );

	if ( $result ) {
		writeAvatarLog( 0, $me, $requestedName, 'gravatar' );
		$body = "<p>Fetched gravatar for user {$requestedName}. [<img height=\"14\" width=\"14\" src=\"{$requestedURL}\" />] [<img height=\"14\" width=\"14\" src=\"{$hostedURL}\" />]</p><p>These images should match.</p>";
	} else {
		$style = "panel-danger";
		$body = "<p>Failed fetching gravatar for user {$requestedName}, confirm email is attached to their system and we don’t have that user already.</p>";
	}
}

// are we supplying query for revoke + name? → write to log, delete permission
if ( isset( $_GET['revoke'] ) and isset( $_GET['name'] ) ) {

	$action = "Revoke Avatar Permission";
	$requestedName = sanitiseName( $_GET['name'] );
	$requestedPath = $avatarKeyPath . '/' . $requestedName;

	if ( file_exists( $requestedPath ) and !is_dir( $requestedPath ) ) {
		writeAvatarLog( 0, $me, $requestedName, 'revoke' );
		$body = "<p>Revoked permission for the user {$requestedName}.</p>";
		unlink( $requestedPath );
	} else {
		$style = "panel-danger";
		$body = "<p>Can not revoke permission for the user {$requestedName}.</p>";
	}
}

// are we supplying query for delete + name? → write to log, delete image
if ( isset( $_GET['delete'] ) and isset( $_GET['name'] ) ) {

	$action = "Remove Avatar";
	$requestedName = sanitiseName( $_GET['name'] );
	$requestedPath = $avatarFilePath . '/' . $requestedName . '.png';

	if ( file_exists( $requestedPath ) and !is_dir( $requestedPath ) ) {
		writeAvatarLog( 0, $me, $requestedName, 'delete' );
		$body = "<p>Removed avatar file for user {$requestedName}.</p>";
		unlink( $requestedPath );
	} else {
		// fancy this message up.
		$style = "panel-danger";
		$body = "<p>Can not remove avatar file for user {$requestedName}.</p>";
	}
}

include_once('includes/header.php');

print "<h1 class=\"text-center\">Admin‐only Avatar Management</h1>";

if ( $body !== "" ) {
print <<<ACTIONMSG
			<article class="panel panel-default {$style}">
				<header class="panel-heading">
					<h3 class="panel-title">{$action}</h3>
				</header>
				<div class="panel-body">
					{$body}
				</div>
			</article>
ACTIONMSG;
}
?>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Grant user permission</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/avatar/">
						<fieldset>
						<input type="hidden" name="grant">
						<div class="form-group"><label class="control-label col-xs-2" for="name">Handle</label><input class="control-input col-xs-6" name="name" placeholder="Nickname"></div>
						<div class="form-group"><input type="submit" class="col-xs-offset-2 btn btn-primary" value="Grant"></div>
						<p>This will give you a URL to share. Tell them to log in, then visit this link and it will upload their avatar to the name you pick for them. Once it has completed, it should redirect them to their avatar on our server.</p>
						</fieldset>
					</form>
				</div>
			</article>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Revoke user permission</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/avatar/">
						<fieldset>
						<input type="hidden" name="revoke">
						<div class="form-group"><label class="control-label col-xs-2" for="name">Handle</label><input class="control-input col-xs-6" name="name" placeholder="Nickname"></div>
						<div class="form-group"><input type="submit" class="col-xs-offset-2 btn btn-primary" value="Revoke"></div>
						<p>This will strip the user of permission to add their Steam avatar. This happens automatically, so only do if needed.</p>
						</fieldset>
					</form>
				</div>
			</article>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Upload avatar</h3>
				</header>
				<div class="panel-body">
					<form method="POST" class="form-horizontal" enctype="multipart/form-data" action="/avatar/">
						<fieldset>
						<input type="hidden" name="MAX_FILE_SIZE" value="500000">
						<div class="form-group"><label class="control-label col-xs-2" for="name">Handle</label><input class="control-input col-xs-6" name="name" placeholder="Nickname"></div>
						<div class="form-group"><label class="control-label col-xs-2" for="userfile">File</label><input class="control-input col-xs-6" type="file" name="userfile" placeholder="/home/you/files-are-here"></div>
						<div class="form-group"><input type="submit" class="col-xs-offset-2 btn btn-primary" value="Upload"></div>
						<p>This will allow you to upload an avatar for the named user. This will not replace the existing avatar.</p>
						</fieldset>
					</form>
				</div>
			</article>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Remove avatar</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/avatar/">
						<fieldset>
						<input type="hidden" name="delete">
						<div class="form-group"><label class="control-label col-xs-2" for="name">Handle</label><input class="control-input col-xs-6" name="name" placeholder="Nickname"></div>
						<div class="form-group"><input type="submit" class="col-xs-offset-2 btn btn-primary btn-danger" value="Remove"></div>
						<p>This will <em style="color:red">REMOVE</em> the avatar for this user. Should be done only if someone wants to replace theirs.</p>
						</fieldset>
					</form>
				</div>
			</article>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Add Gravatar</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/avatar/">
						<fieldset>
						<div class="form-group"><label class="control-label col-xs-2" for="name">Handle</label><input class="control-input col-xs-6" name="name" placeholder="Nickname"></div>
						<div class="form-group"><label class="control-label col-xs-2" for="email">Email</label><input class="control-input col-xs-6" name="email" type="email" placeholder="webmaster@example.com"></div>
						<div class="form-group"><input type="submit" class="col-xs-offset-2 btn btn-primary" value="Gravatar"></div>
						<p>This will upload the image for this email address to the file /avatars/handle.png.</p>
						</fieldset>
					</form>
				</div>
			</article>

			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Logfile</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/avatar/">
						<fieldset>
						<div class="form-group"><label class="control-label col-xs-2">Anything changed?</label><input type="submit" class="btn btn-primary" value="Refresh"></div>
						</fieldset>
					</form>
					<textarea class="form-control" rows="10"><?=readAvatarLog(); ?></textarea>
				</div>
			</article>
<?php include_once('includes/footer.php');

// TODO improve log, convert into a table?
// TODO add functions_steam support to the admin log
// TODO List active permission slips.
// TODO List existing avatars?
// TODO allow users with valid permission slip to overwrite their current avatar

