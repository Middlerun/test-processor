<?php
  $first = $_POST['first'];
  $each = $_POST['each'];
  $last = $_POST['last'];
  $input = $_POST['input'];
  $output = "";

  ob_start();
  try {
    eval($first);
    $lines = preg_split("/\r?\n/", $input);
    foreach ($lines as $line) {
      eval($each);
    }
    eval($last);
    $output = ob_get_clean();
  }
  catch (Exception $ex) {
    ob_end_clean();
    $output = "Error";
  }
?>
<!doctype html>

<html>
  <head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/php/php.min.js"></script>
    <style>
      body {
        background-color: #333;
        color: white;
        margin: 20px;
      }
      .inputs-container {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        grid-gap: 20px;
      }
      .code-input-container {
        grid-column: span 2;
      }
      .text-input-container {
        grid-column: span 3;
      }
      .submit-row {
        grid-column: 1 / -1;
        display: flex;
        justify-content: space-between;
      }
      .submit-row > div {
        display: inline-block;
      }
      .submit-save > * + * {
        margin-left: 20px;
      }
    </style>
  </head>
  <body>
    <h1>Text Processor</h1>
    <form method="post">
      <div class="inputs-container">
        <div class="code-input-container">
          <h2>First</h2>
          <textarea name="first" id="first"><?=htmlentities($first)?></textarea>
        </div>
        <div class="code-input-container">
          <h2>foreach ($lines as $line)</h2>
          <textarea name="each" id="each"><?=htmlentities($each)?></textarea>
        </div>
        <div class="code-input-container">
          <h2>Last</h2>
          <textarea name="last" id="last"><?=htmlentities($last)?></textarea>
        </div>
        <div class="text-input-container">
          <h2>Input</h2>
          <textarea name="input" id="input"><?=htmlentities($input)?></textarea>
        </div>
        <div class="text-input-container">
          <h2>Output</h2>
          <textarea name="output" id="output"><?=htmlentities($output)?></textarea>
        </div>
        <div class="submit-row">
          <div class="submit-save">
            <input type="submit" value="Submit">
          </div>
        </div>
      </div>
    </form>

    <script>
      const codeEditorNames = ['first', 'each', 'last'];
      const textEditorNames = ['input', 'output'];
      const editors = {};

      codeEditorNames.forEach(function (name) {
        editors[name] = CodeMirror.fromTextArea(document.getElementById(name), {
          lineNumbers: true,
          matchBrackets: true,
          mode: 'text/x-php',
        });
      });

      textEditorNames.forEach(function (name) {
        editors[name] = CodeMirror.fromTextArea(document.getElementById(name), {
          lineNumbers: true,
          matchBrackets: true,
        });
      });

    </script>
  </body>
</html>
