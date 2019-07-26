<?php 

/*
 * Sorts by timestamp field.
 */
function sortByDate($a, $b) {
    $d1 = new DateTime($a["timestamp"]);
    $d2 = new DateTime($b["timestamp"]);
    if ($d1 !== false && $d2 !== false) return ($d1 < $d2 ? -1 : ($d1 > $d2 ? 1 : 0)) ;
    else if ($d1 === false && $d2 !== false) return 1;
    else if ($d1 !== false && $d2 === false) return -1;
    else return 0;
}

$result = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logs'])) {
        $logs = $_POST['logs'];
        $pattern = "/^.*?X-Forwarded-For:\s*\d+\.\d+\.\d+\.\d+,\s*X-Real-IP:\s*\d+\.\d+\.\d+\.\d+\s*\]\s*(\{.+\})/i";
        $lines = explode(PHP_EOL, $logs);
        $i = 0;
        $data = array();

        // Parse input
        foreach($lines as $line) {
            $text = trim($line);
            if (strlen($text) > 0) {
                preg_match($pattern, $text, $matches);
                $json_str = $matches[1];
                $json = json_decode($json_str, true);
                if (is_array($json)) {
                    $json['source'] = $text;
                    array_push($data, $json);
                } else {
                    // Create an entry for parsing errors
                    $parse_error = array();
                    $parse_error['message']   = 'Unparsable line';
                    $parse_error['timestamp'] = '';
                    $parse_error['level']   = 'PARSE';
                    $parse_error['source']  = $text;
                    array_push($data, $parse_error);
                }
            }
        }

        // Sort by date using sortByDate function
        usort($data, 'sortByDate');

        // Generate HTML
        foreach($data as $json) {
            $fdate = "";
            if (isset($json["timestamp"])) {
                $date = new DateTime($json["timestamp"]);
                if ($date !== false) {
                    $fdate = $date->format('M j, Y H:i:s.v');
                }
            }
            $small = "";
            if (isset($json["appid"])) {
                $small .= "<small><strong>App Id</strong>: " . $json["appid"] . "</small><br>";
            }
            if (isset($json["service"])) {
                $small .= "<small><strong>Service</strong>: " . $json["service"] . "</small><br>";
            }
            if (isset($json["region"])) {
                $small .= "<small><strong>Region</strong>: " . $json["region"] . "</small><br>";
            }
            if (isset($json["environment"])) {
                $small .= "<small><strong>Environment</strong>: " . $json["environment"] . "</small><br>";
            }
            if (strlen($small) > 0) {
                $small = "<p>" . $small . "</p>";
            }
            $color = "";
            switch ($json["level"]) {
                case "DEBUG" : $color = "badge badge-secondary"; break;
                case "INFO"  : $color = "badge badge-info"; break;
                case "WARN"  : $color = "badge badge-warning"; break;
                case "ERROR" : $color = "badge badge-danger"; break;
                case "PARSE" : $color = "badge badge-dark"; break;
                default      : $color = "";
            }
            $result .= "<tr>";
            $result .= '<td><span class="'.$color.'">' . htmlspecialchars($json["level"]) . "</span></td>"; 
            $result .= "<td>" . $fdate . "</td>"; 
            $result .= "<td>" . $json["logger_name"] . $small . "</td>"; 
            $result .= '<td class="breakable">' . htmlspecialchars($json["message"]) . '<p><a href="#" onclick="$(\'#fullentry' . $i . '\').slideToggle();return false;">Show/hide full entry</a></p><div id="fullentry' . $i . '" style="display:none">' . htmlspecialchars($json['source']) . '</div></td>';
            $result .= "<tr>";
            $i++;
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>Loggly logs</title>
    <style>
        td, th {
          font-size: 0.9rem;          
        }
        td.breakable {
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Clean up Live Tail Loggly logs</h1>
        <form method="post">
            <div class="form-group">
                <label for="logs">Paste your logs here.</label>
                <textarea name="logs" class="form-control" id="logs" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <?php if (strlen($result) > 0) { ?>
            <br><br>
            <div>
                <h2>Results</h2>
                <p>Sorted by timestamp, newest at the top. If a line cannot be parsed, it will display PARSE on the Level colum.</p>
                <table class="table table-striped">
                    <thead><tr>
                        <th>Level</th>
                        <th>Timestamp</th>
                        <th>Logger name</th>
                        <th>Message</th>
                    </tr></thead>
                    <tbody><?php echo $result; ?></tbody>
                </table>
            </div>
        <?php } ?>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>

</html>
