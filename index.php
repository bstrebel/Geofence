<?php
/*
 * File: geofence/index.php Rev. 1.0
 * Copyright (c) 2015 Bernd Strebel (b.strebel@ebs-foto.de)
 * based on cron plugin from Willy Fritzsche
 *
 */
if (!(isset($_GET['edit']) && $_GET['edit'] != ''))
{
?>
<style type="text/css">

textarea {
	display: none;
}

.geofence_edit_save {
	display: none;
}
</style>
<noscript>
	<style type="text/css">
		textarea {
			display: block;
		}
		
		.geofence_edit_confirm {
			display: none;
		}
		
		.geofence_edit_save {
			display: block;
		}
	</style>
</noscript>
<?php

    // Geofence service update
    if ( isset($_GET['update'], $_GET['zone']) && $_GET['update'] != '' && $_GET['zone'] != '' ) {

        $zone = $_GET['zone'];
        $update = $_GET['update'];
        $device = isset($_GET['device']) ? $_GET['device'] : '*';

        if ( file_exists($plugin_folder_absolute.'/config/actions')) {

            $lines = file($plugin_folder_absolute.'/config/actions');

            foreach ($lines as $line) {

                $line = trim($line);
                if (substr($line, 0, 1) == '#' || $line == '')
                {
                    continue;
                }
                else {

                    $result = explode(':', $line, 4);

                    if ( strcasecmp($zone, $result[0])   == 0 &&
                         strcasecmp($update, $result[1]) == 0 &&
                         strcasecmp($device, $result[2]) == 0
                    ) {

                        // execute update command
                        $output = array();
                        $return = 0;

                        $command = trim($result[3]);
                        $command = str_replace('%zone%', $zone, $command);
                        $command = str_replace('%update%', $update, $command);
                        $command = str_replace('%device%', $device, $command);

                        $out = exec($command, $output, $return);

                        if ( ! isset($_GET['no_html'])) {

                            $tpl = new RainTPL;
                            $tpl->msg('green','Geofence', implode('',$output));
                        }
                        else {

                            if ( isset($_GET['return']) ) {

                                switch(strtolower($_GET['return'])) {

                                    case "json":

                                        $data = array(

                                            "status" => 200,
                                            "return" => $return,
                                            "output" => implode('',$output),
                                            "exec" => $command,
                                            "zone" => $result[0],
                                            "update" => $result[1],
                                            "device" => $result[2],
                                            "command" => $result[3],
                                        );

                                        print(json_encode($data));
                                        break;

                                    case "nothing":
                                    case "none":
                                    case "null":

                                        break;

                                    default:
                                        print(implode('',$output));

                                }
                            }
                        }

                        exit($return);
                    }
                }
            }
        }

        // No action found for zone-update-device combination

        $message = 'Keine Aktionen für [' . $_GET['zone'] . '/' . $_GET['update'] . '] gefunden!';

        if ( ! isset($_GET['no_html'])) {

            $tpl = new RainTPL;
            $tpl->error('Geofence', $message);
        }
        else {

            print('Geofence: '.$message);
        }
        exit(1);
    }

	// Aktion löschen
	if (isset($_GET['geofence'], $_GET['delete']) && $_GET['geofence'] != '' && $_GET['delete'] == '')
	{
        $lines = file($plugin_folder_absolute.'/config/actions');
		$new_file = '';
		$line_count = 0;
		
		foreach ($lines as $line)
		{
            $line = trim($line);
            if ($line_count != $_GET['geofence'])
			{
				$new_file .= $lines[$line_count] . "\n";
			}
			$line_count += 1;
		}
		
		$file = fopen($plugin_folder_absolute.'/temp/actions.tmp', 'w+');
		fwrite($file, $new_file);
		fclose($file);
		
		if ( copy($plugin_folder_absolute.'/temp/actions.tmp', $plugin_folder_absolute.'/config/actions') )
		{
			unlink($plugin_folder_absolute.'/temp/actions.tmp');
			header('Location: ?s=plugins&id=geofence&delete=ready');
			exit();
		}
		else
		{
			$info_message = array('red', 'Fehler beim speichern der Konfiguration!');
		}
	}
	elseif (isset($_GET['delete']) && $_GET['delete'] == 'ready')
	{
		$info_message = array('green', 'Aktion wurde erfolgreich gelöscht.');
	}
	
	// Aktion hinzufügen
	if (isset($_GET['add']) && $_GET['add'] == '')
	{
		if ($_POST['zone'] != '' && $_POST['update'] != '' && $_POST['command'] != '')
		{
            $new_file = '';

            foreach(file($plugin_folder_absolute.'/config/actions') as $line) {

                $line = trim($line);
                $new_file .= $line . "\n";
            }

            $data = array();
            $data[] = $_POST['zone'];
            $data[] = $_POST['update'];
            $data[] = ( $_POST['device'] != "" ) ? $_POST['device'] : '*';
            $data[] = $_POST['command']."\n";

            $lines[] = implode(':',$data);

			$file = fopen($plugin_folder_absolute.'/temp/actions.tmp', 'w+');
			fwrite($file, $new_file);
			fclose($file);

            if ( copy($plugin_folder_absolute.'/temp/actions.tmp', $plugin_folder_absolute.'/config/actions') )
			{
				unlink($plugin_folder_absolute.'/temp/actions.tmp');
				header('Location: ?s=plugins&id=geofence&add=ready');
				exit();
			}
			else
			{
                $info_message = array('red', 'Fehler beim speichern der Konfiguration!');
			}
		}
		else
		{
			$info_message = array('red', 'Bitte fülle alle Felder aus!');
		}
	}
	elseif (isset($_GET['add']) && $_GET['add'] == 'ready')
	{
		$info_message = array('green', 'Aktion wurde erfolgreich hinzugefügt.');
	}
	
	// Aktion manuell speichern
	if (isset($_GET['manual']) && $_GET['manual'] == 'save')
	{
		if ($_POST['geofence_manual'] != '')
		{
            $lines = file($plugin_folder_absolute.'/config/actions');
			
			$file = fopen($plugin_folder_absolute.'/temp/actions.tmp', 'w+');
			fwrite($file, str_replace("\r\n", "\n", $_POST['geofence_manual']));
			fclose($file);

            if ( copy($plugin_folder_absolute.'/temp/actions.tmp', $plugin_folder_absolute.'/config/actions') )
			{
				unlink($plugin_folder_absolute.'/temp/actions.tmp');
				header('Location: ?s=plugins&id=geofence&manual=ready');
				exit();
			}
			else
			{
                $info_message = array('red', 'Fehler beim speichern der Konfiguration!');
			}
		}
		else
		{
			$info_message = array('red', 'Bitte gebe mindestens ein Zeichen ein! Tipp: "#"');
		}
	}
	elseif (isset($_GET['manual']) && $_GET['manual'] == 'ready')
	{
		$info_message = array('green', 'Aktion wurde erfolgreich manuell gespeichert.');
	}
	
	// Aktionen auslesen
    $lines = file($plugin_folder_absolute.'/config/actions');
	$output = array();
	for ($i = 0; $i < count($lines); $i += 1)
	{
        $lines[$i] = trim($lines[$i]);
		if ( substr($lines[$i], 0, 1) == '#' || $lines[$i] == '')
		{
			continue;
		}
		else
		{
			$result = explode(':', $lines[$i], 5);
			$result[] = $i;
			$output[] = $result;
		}
	}
	
	if (isset($info_message))
	{
?>
<div id="message_box">
	<div class="<?php echo 'info_'.$info_message[0].' '; ?>box">
		<div class="inner">
			<img src="public_html/img/delete.png" style="float: right; margin: -15px -15px 0px 0px; opacity: 0.6; width: 16px;" onClick="document.getElementById('message_box').style.display='none'" />
			<strong><?php echo $info_message[1]; ?></strong>
		</div>
	</div>
</div>
<?php
	}
?>
<div>
	<div class="box">
		<div class="inner-header">
			<span>Geofence Aktionen</span>
		</div>
		<div class="inner">
			<table class="table">
				<tr>
					<th style="width: 10%;" title="Zone">Zone</th>
                    <th style="width: 10%;" title="Update">Update</th>
                    <th style="width: 10%;" title="Device">Device</th>
					<th style="width: 63%;" title="Befehl">Befehl</th>
					<th style="width: 7%;"></th>
				</tr>
<?php
	foreach ($output as $data)
	{
?>
				<tr>
					<td><?php echo $data[0]; ?></td>
					<td><?php echo $data[1]; ?></td>
					<td><?php echo $data[2]; ?></td>
					<td><?php echo $data[3]; ?></td>
					<td style="text-align: center; vertical-align: middle;"><a href="?s=plugins&amp;id=geofence&amp;edit=<?php echo $data[4]; ?>" style="margin-right: 8px;"><img src="<?php echo $plugin_folder_html; ?>/images/edit.png" style="width: 16px; vertical-align: middle;" /></a><a href="?s=plugins&amp;id=geofence&amp;geofence=<?php echo $data[4]; ?>&amp;delete"><img src="<?php echo $plugin_folder_html; ?>/images/delete.png" style="width: 16px; vertical-align: middle;" /></a></td>
				</tr>
<?php
	}
?>
			</table>
		</div>
	</div>
	<div class="box">
		<div class="inner-header">
			<span>Geofence Aktion hinzufügen</span>
		</div>
		<form action="?s=plugins&amp;id=geofence&amp;add" method="post">
			<div class="inner-bottom">
				<table style="width: 100%; border-spacing: 0px;">
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Zone">Zone</td>
						<td style="padding: 5px;"><input name="zone" type="text" style="width: 350px;" /></td>
					</tr>
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Update">Update</td>
						<td style="padding: 5px;"><input name="update" type="text" style="width: 350px;" /></td>
					</tr>
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Device">Device</td>
						<td style="padding: 5px;"><input name="device" type="text" style="width: 350px;" /></td>
					</tr>
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Befehl">Befehl</td>
						<td style="padding: 5px;"><input name="command" type="text" style="width: 770px;" /></td>
					</tr>
				</table>
			</div>
			<div class="inner">
				<input type="submit" name="submit" value="Hinzufügen" />
			</div>
		</form>
	</div>
	<div class="box">
		<div class="inner-header">
			<span>Aktionen manuell bearbeiten</span>
		</div>
		<form action="?s=plugins&amp;id=geofence&amp;manual=save" method="post">
			<div class="inner-bottom" style="padding: 2px;">
				<textarea name="geofence_manual" style="width: 100%; height: 400px; border: 0px; resize: vertical; padding: 0px; box-shadow: none;"><?php foreach ($lines as $line) { echo $line."\n"; } ?></textarea>
				<div class="geofence_edit_confirm" style="padding: 18px;"><a onClick="return ((confirm('Willst du die Aktionen wirklich selber bearbeiten? Falsche Eingaben können Fehler verursachen!') == false) ? false : jQuery('.geofence_edit_save, textarea').show().parent().find('.geofence_edit_confirm').hide())">Aktionen manuell bearbeiten</a></div>
			</div>
			<div class="inner geofence_edit_save">
				<input type="submit" value="Speichern" />
			</div>
		</form>
	</div>
</div>
<div class="clear_both"></div>
<?php
}
else // END = Aktionen // START = Editieren
{
	// Aktion speichern
	if (isset($_GET['save']) && $_GET['save'] == '')
	{
		if ($_POST['zone'] != '' && $_POST['update'] != '' && $_POST['command'] != '')
		{
            $lines = file($plugin_folder_absolute.'/config/actions');
			$new_file = '';
			$line_count = 0;
			
			foreach ($lines as $line)
			{
				$line = trim($line);
                if ($line_count == $_GET['edit'])
				{
                    $data = array();
                    $data[] = $_POST['zone'];
                    $data[] = $_POST['update'];
                    $data[] = ( $_POST['device'] != "" ) ? $_POST['device'] : '*';
                    $data[] = $_POST['command'];

                    $new_file .= implode(':',$data);
                    $new_file .= "\n";
				}
				else
				{
					$new_file .= $lines[$line_count];
                    $new_file .= "\n";
				}
				$line_count += 1;
			}
			
			$file = fopen($plugin_folder_absolute.'/temp/actions.tmp', 'w+');
			fwrite($file, $new_file);
			fclose($file);

            if ( copy($plugin_folder_absolute.'/temp/actions.tmp', $plugin_folder_absolute.'/config/actions') )
			{
				unlink($plugin_folder_absolute.'/temp/actions.tmp');
				header('Location: ?s=plugins&id=geofence&edit='.$_GET['edit'].'&save=ready');
				exit();
			}
			else
			{
                $info_message = array('red', 'Fehler beim speichern der Konfiguration!');
			}
		}
		else
		{
			$info_message = array('red', 'Bitte fülle alle Felder aus!');
		}
	}
	elseif (isset($_GET['save']) && $_GET['save'] == 'ready')
	{
		$info_message = array('green', 'Aktion wurde erfolgreich gespeichert.');
	}
	
	// Aktionen auslesen
    $lines = file($plugin_folder_absolute.'/config/actions');
	$result = explode(':', trim($lines[$_GET['edit']]), 5);
	
	if (isset($info_message))
	{
?>
<div id="message_box">
	<div class="<?php echo 'info_'.$info_message[0].' '; ?>box">
		<div class="inner">
			<img src="public_html/img/delete.png" style="float: right; margin: -15px -15px 0px 0px; opacity: 0.6; width: 16px;" onClick="document.getElementById('message_box').style.display='none'" />
			<strong><?php echo $info_message[1]; ?></strong>
		</div>
	</div>
</div>
<?php
	}
?>
<div>
<div class="box">
		<div class="inner-header">
			<span>Aktionen bearbeiten</span>
			<?php echo showGoBackIcon('?s=plugins&amp;id=geofence'); ?>
		</div>
		<form action="?s=plugins&amp;id=geofence&amp;edit=<?php echo $_GET['edit']; ?>&amp;save" method="post">
			<div class="inner-bottom">
				<table style="width: 100%; border-spacing: 0px;">
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Zone">Zone</td>
						<td style="padding: 5px;"><input type="text" name="zone" style="width: 350px;" value="<?php echo $result[0]; ?>" /></td>
					</tr>
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Update">Update</td>
						<td style="padding: 5px;"><input type="text" name="update" style="width: 350px;" value="<?php echo $result[1]; ?>" /></td>
					</tr>
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Device">Device</td>
						<td style="padding: 5px;"><input type="text" name="device" style="width: 350px;" value="<?php echo $result[2]; ?>" /></td>
					</tr>
					<tr>
						<td style="width: 20%; font-weight: bold; padding: 5px;" title="Befehl">Befehl</td>
						<td style="padding: 5px;"><input type="text" name="command" style="width: 770px;" value="<?php echo htmlentities($result[4], ENT_QUOTES); ?>" /></td>
					</tr>
				</table>
			</div>
			<div class="inner">
				<input type="submit" name="submit" value="Speichern" />
			</div>
		</form>
	</div>
</div>
<?php
}
?>