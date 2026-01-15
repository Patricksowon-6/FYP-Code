<?php
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Operator</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        #messageContainer {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }
        .word {
            position: relative;
            white-space: nowrap; /* Ensures that the word doesn't wrap and stays on one line */
        }
        .letter {
            opacity: 0;
            font-size: 36px;
            font-weight: bold;
            color: #333333;
            transition: opacity 2s ease;
            display: inline-block;
        }
        #welcome {
            margin-left: -50px; /* Moves "Welcome" further to the left */
        }
        #operator {
            margin-left: 20px; /* Indent "Operator" relative to "Welcome" */
        }
    </style>
</head>
<body>
    <div id="messageContainer">
        <div id="welcome" class="word"></div>
        <div id="operator" class="word"></div>
    </div>

    <script>
        function createLetterSpans(word, containerId, delayOffset) {
            const container = document.getElementById(containerId);
            word.split('').forEach((char, index) => {
                const span = document.createElement('span');
                span.classList.add('letter');
                span.textContent = char;
                container.appendChild(span);

                setTimeout(() => {
                    span.style.opacity = 1;
                }, 100 * (index + delayOffset));
            });
        }

        window.onload = function() {
            const username = <?= json_encode($_SESSION['user_name'] ?? 'Operator'); ?>;

            createLetterSpans("Welcome", "welcome", 0);
            createLetterSpans(username, "operator", 8);

            setTimeout(function() {
                window.location.href = '<?= PAGES_URL; ?>logged_in.php';
            }, 3500);
        };
    </script>

</body>
</html>
