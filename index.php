<?php
  /*
   * Text Processor
   * A tool for processing text using PHP code typed directly into the browser.
   * Due to the use of eval() I would not recommend hosting this publicly,
   * unless you want your server to be hacked.
   */

  // Constants
  $SAVE_FILE = "saved_scripts.json";
  $DEFAULTS = [
    'first' => "",
    'each' => "",
    'last' => "",
    'input' => "a\tfoo\nn\tbar",
    'output' => "",
  ];

  // Read in POST data
  $first = $_POST['first'];
  $each = $_POST['each'];
  $last = $_POST['last'];
  $input = $_POST['input'];
  $save_name = substr(preg_replace("/[^a-zA-Z0-9\-_]/", "", $_POST['savename']), 0, 50);
  $load_name = $_POST['loadname'];
  $output = "";
  $save_error = null;
  $load_error = null;

  // Set defaults
  if (!$_POST['submitted']) {
    $first = "// Code that is executed at the start\n\n" .
      "echo \"\\\$example = [\" . PHP_EOL;";
    $each = "// Code that is executed for each input line\n\n" .
      "\$elements = explode(\"\\t\", \$line);\n" .
      "echo \"  '{\$elements[0]}' => \\\"{\$elements[1]}\\\"\" . PHP_EOL;";
    $last = "// Code that is executed at the end\n\n" .
      "echo \"];\";";
    $input = "a\tfoo\nb\tbar";
  }

  // Get saved scripts
  $saved_scripts_json = file_get_contents($SAVE_FILE);
  if ($saved_scripts_json) {
    $saved_scripts = json_decode($saved_scripts_json, true);
  }
  if (!$saved_scripts_json || is_null($saved_scripts)) {
    $saved_scripts = [];
  }

  // Save script
  if ($_POST['save']) {
    if (strlen($save_name) > 0) {
      $new_saved_scripts = $saved_scripts;
      $new_saved_scripts[$save_name] = [
        'first' => $first,
        'each' => $each,
        'last' => $last
      ];
      $saved = file_put_contents($SAVE_FILE, json_encode($new_saved_scripts, JSON_PRETTY_PRINT));
      if ($saved) {
        $saved_scripts = $new_saved_scripts;
      } else {
        $save_error = "Unable to write save file";
      }
    } else {
      $save_error = "Save name invalid";
    }
  }

  if ($_POST['load']) {
    // Load script
    if (array_key_exists($load_name, $saved_scripts)) {
      $script_to_load = $saved_scripts[$load_name];
      $first = $script_to_load['first'];
      $each = $script_to_load['each'];
      $last = $script_to_load['last'];
    } else {
      $load_error = "Unable to load script \"" . htmlentities($load_name) . "\"";
    }
  } elseif ($_POST['delete']) {
    // Delete script
    if (array_key_exists($load_name, $saved_scripts)) {
      $new_saved_scripts = $saved_scripts;
      unset($new_saved_scripts[$load_name]);
      $deleted = file_put_contents($SAVE_FILE, json_encode($new_saved_scripts, JSON_PRETTY_PRINT));
      if ($deleted) {
        $saved_scripts = $new_saved_scripts;
      } else {
        $load_error = "Couldn't delete script \"" . htmlentities($load_name) . "\" - unable to write save file";
      }
    }
  } else {
    // Do the thing
    ob_start();
    try {
      eval($first);
      $lines = preg_split("/\r?\n/", $input);
      foreach ($lines as $line) {
        eval($each);
      }
      eval($last);
      $output = ob_get_clean();
    } catch (Exception $ex) {
      ob_end_clean();
      $output = "Error";
    }
  }
?>
<!doctype html>

<html>
  <head>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/php/php.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans|IBM+Plex+Mono" rel="stylesheet">
  </head>
  <body>
    <form method="post">
      <div class="main-grid">
        <div class="title-row">
          <h1>Text Processor</h1>
          <span><a href="https://github.com/Middlerun/text-processor" target="_blank">Made by Middlerun</a></span>
        </div>

        <div class="code-input-container">
          <h2>First</h2>
          <div>
            <textarea name="first" id="first"><?=htmlentities($first)?></textarea>
          </div>
        </div>
        <div class="code-input-container">
          <h2>foreach ($lines as $line)</h2>
          <div>
            <textarea name="each" id="each"><?=htmlentities($each)?></textarea>
          </div>
        </div>
        <div class="code-input-container">
          <h2>Last</h2>
          <div>
            <textarea name="last" id="last"><?=htmlentities($last)?></textarea>
          </div>
        </div>
        <div class="text-input-container">
          <h2>Input</h2>
          <div>
            <textarea name="input" id="input"><?=htmlentities($input)?></textarea>
          </div>
        </div>
        <div class="text-input-container">
          <h2>Output</h2>
          <div>
            <textarea id="output"><?=htmlentities($output)?></textarea>
          </div>
        </div>

        <div class="submit-row">
          <div class="submit-save">
            <input type="submit" name="submit" value="Submit">
            <span>
              <label for="save">
                <input type="checkbox" name="save" id="save"/>
                Save as
              </label>
              <input type="text" name="savename" value="<?=htmlentities($save_name)?>"/>
            </span>
            <?php if ($save_error): ?>
              <span class="error-message"><?=$save_error?></span>
            <?php endif; ?>
          </div>

          <div>
            <?php if ($load_error): ?>
              <span class="error-message"><?=$load_error?></span>
            <?php endif; ?>
            <?php if (count($saved_scripts) > 0): ?>
              <select name="loadname">
                <option value="">-- Saved scripts --</option>
                <?php foreach (array_keys($saved_scripts) as $key): ?>
                  <option value="<?=$key?>"><?=$key?></option>
                <?php endforeach; ?>
              </select>
              <input type="submit" name="load" value="Load">
              <input type="submit" name="delete" value="Delete">
            <?php endif; ?>
          </div>
        </div>
      </div>
      <input type="hidden" name="submitted" value="true"/>
    </form>

    <script>
      const codeEditorNames = ['first', 'each', 'last'];
      const textEditorNames = ['input', 'output'];

      codeEditorNames.forEach(function (name) {
        CodeMirror.fromTextArea(document.getElementById(name), {
          lineNumbers: true,
          matchBrackets: true,
          mode: 'text/x-php',
        });
      });

      textEditorNames.forEach(function (name) {
        CodeMirror.fromTextArea(document.getElementById(name), {
          lineNumbers: true,
          matchBrackets: true,
        });
      });
    </script>
  </body>
</html>
