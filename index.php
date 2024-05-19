<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu and Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #D8AE7E; 
        }
        .container {
            width: 400px;
            text-align: center;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            margin-bottom: 20px;
        }
        .menu {
            margin-bottom: 20px;
            text-align: left;
        }
        .total-cost {
            display: none;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    session_start();

    // Database connection
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "user";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Registration process
    if (isset($_POST['register'])) {
        $new_username = $_POST['new_username'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_username, $new_password);

        if ($stmt->execute()) {
            echo '<p style="color:green;">Registration successful! You can now log in.</p>';
        } else {
            echo '<p style="color:red;">Error: ' . $stmt->error . '</p>';
        }

        $stmt->close();
    }

    // Login process
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
        } else {
            echo '<p style="color:red;">Invalid username or password</p>';
        }

        $stmt->close();
    }

    // Logout process
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        echo '<p style="color:green;">You have been logged out successfully.</p>';
    }

    // Check if user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ?>
        <div class="form-container">
            <h1>Login</h1>
            <form method="post" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>
                <button type="submit" name="login" style="background-color: #FF5733;">Login</button>
            </form>
        </div>

        <div class="form-container">
            <h1>Register</h1>
            <form method="post" action="">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="new_username" required><br>
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="new_password" required><br>
                <button type="submit" name="register" style="background-color: #FF5733;">Register</button>
            </form>
        </div>
    <?php
    } else {
    ?>
        <h1>Welcome to the Engkanto Eatery, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Here are the Menu and the Prices:</p>
        <ul class="menu">
            <li>Pares Overload - 99 PHP</li>
            <li>Meals w/ Rice (Any Ulam) - 75 PHP</li>
            <li>SingkitJoy - 50 PHP</li>
            <li>Coke - 20 PHP</li>
            <li>Iced Tea - 15 PHP</li>
        </ul>

        <form id="order-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="menu">Menu:</label>
            <select id="menu" name="menu">
                <option value="Pares Overload">Pares Overload</option>
                <option value="Meals w/ Rice">Meals w/ Rice (Any Ulam)</option>
                <option value="SingkitJoy">SingkitJoy</option>
                <option value="Coke">Coke</option>
                <option value="Iced Tea">Iced Tea</option>
            </select><br>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" value="1"><br>

            <label for="cash">Cash:</label>
            <input type="number" id="cash" name="cash" min="0" step="0.01"><br>

            <button type="submit" id="submit-button" style="background-color: #FF5733;">Submit</button>
        </form>

        <form method="post" action="">
            <button type="submit" name="logout" style="background-color: #FF5733;">Logout</button>
        </form>

        <div class="total-cost">
            <h2 id="total-cost-heading">Total Cost of the order:</h2>
            <p id="total-cost"></p>
            <h2 id="change-heading">Your Change:</h2>
            <p id="change"></p>
            <p>Thank you for Ordering! Come Back Again~</p>
        </div>
    <?php
    }
    
    // Close database connection
    $conn->close();
    ?>
</div>

<script>
    const menuPrices = {
        "Pares Overload": 99,
        "Meals w/ Rice": 75,
        "SingkitJoy": 50,
        "Coke": 20,
        "Iced Tea": 15
    };

    const orderForm = document.getElementById("order-form");
    const submitButton = document.getElementById("submit-button");
    const totalCostDiv = document.querySelector(".total-cost");
    const totalCostHeading = document.getElementById("total-cost-heading");
    const totalCostPara = document.getElementById("total-cost");
    const changeHeading = document.getElementById("change-heading");
    const changePara = document.getElementById("change");

    orderForm.addEventListener("submit", function(event) {
        event.preventDefault();
        const formData = new FormData(orderForm);
        const selectedMenu = formData.get("menu");
        const quantity = parseInt(formData.get("quantity"));
        const cash = parseFloat(formData.get("cash"));

        const totalCost = menuPrices[selectedMenu] * quantity;
        const change = cash - totalCost;

        totalCostHeading.innerHTML = "";
        totalCostPara.textContent = "";
        changeHeading.innerHTML = "";
        changePara.textContent = "";

        totalCostDiv.style.display = "block";
        submitButton.style.backgroundColor = "#66CC66"; 

        const resultWindow = window.open('', '_blank');
        resultWindow.document.write('<h2>Total Cost of the order:</h2>');
        resultWindow.document.write('<p>' + totalCost + ' PHP</p>');
        resultWindow.document.write('<h2>Your Change:</h2>');
        resultWindow.document.write('<p>' + change + ' PHP</p>');
        resultWindow.document.write('<p>Thank you for Ordering! Come Back Again~</p>');
        resultWindow.document.body.style.backgroundColor = '#D8AE7E'; 

        resultWindow.focus();
    });
</script>

</body>
</html>




