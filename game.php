<?php
    session_start();

    // Used to show detailed errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    // Checks if rand num is set, if not - sets random num.
    // Initialises attempts to 0
    if (!isset($_SESSION['random_number'])) {
        $_SESSION['random_number'] = rand(1, 100);
        $_SESSION['attempts'] = 0;
    }
    
    // Checking if form has been submitted - if so retrieve guess
    // and increment attempts.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $guess = $_POST['guess'];
        $_SESSION['attempts']++;

        // Validate the guess
        if (!is_numeric($guess) || $guess < 1 || $guess > 100) {
            $result = 'Invalid input! Please enter a number between 1 and 100.';
            $_SESSION['attempts']--; // Don't count invalid guesses
        }        

        else{
            // Checking if guessed number is correct, lower or higher
            if ($guess < $_SESSION['random_number']) {
                $result = 'Too Low! Try again.';
            }
            elseif ($guess > $_SESSION['random_number']) {
                $result = 'Too High! Try again.';
            }
            else {
                $result = "Congratulations! You guessed the number {$_SESSION['random_number']} in {$_SESSION['attempts']} attempts.";

                // Get the current date and time
                $timestamp = date('Y-m-d H:i:s');
                
                // Prepare the line to store in guesses.txt
                $line = "Guessed: {$_SESSION['random_number']} in {$_SESSION['attempts']} attempts on $timestamp\n";
                
                // Append the line to guesses.txt
                file_put_contents('guesses.txt', $line, FILE_APPEND);

                // Check if current attempts is a high score
                $leaderboard = 'leaderboard.txt';
                $best_score = null;

                // Load current best score, if any
                if (file_exists($leaderboard)) {
                    $best_score = (int)file_get_contents($leaderboard);
                }

                // Update leaderboard if this attempt is better
                if ($best_score === null || $_SESSION['attempts'] < $best_score) {
                    file_put_contents($leaderboard, $_SESSION['attempts']);
                    $result .= " New high score!";
                }

                // Clear the session to reset the game
                session_unset();
                session_destroy();

                // Set a flag for JavaScript redirect after 5 seconds
                $redirect = true;
            }
        }
    }
    
 ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <title>Guess the Number Game</title>
</head>
<body>

    <div class="container"> <!-- Start of container -->

        <h1>Guess the Number!</h1>

        <?php if (isset($result)): ?>
            <p id="result"><?php echo htmlspecialchars($result); ?></p>
        <?php endif; ?>
        
        <!-- Guess Input Form (added this part) -->
        <?php if (!isset($redirect)): ?>
                    <form action="game.php" method="post">
                        <input type="number" name="guess" min="1" max="100" required>
                        <button type="submit">Submit Guess</button>
                    </form>
                <?php endif; ?>

        <!-- Reset Form -->
        <form action="reset.php" method="post">
            <button type="submit">Reset Game</button>
        </form>

        <!-- Leaderboard -->
        <div class="leaderboard">
            <h3>Best Score:</h3>
            <p>
                <?php
                echo file_exists('leaderboard.txt') ? 'Best score: ' . file_get_contents('leaderboard.txt') . ' attempts' : 'No scores yet!';
                ?>
            </p>
        </div>

    <!-- Link to play again -->
    <a href="index.html">Play Again</a>

    </div> <!-- End of container -->

    <?php if (isset($redirect)): ?>
        <script>
            setTimeout(function() {
                window.location.href = "index.html";
            }, 5000);
        </script>
    <?php endif; ?>
</body>
</html>
