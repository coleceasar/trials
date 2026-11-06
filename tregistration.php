<html>
    <head>
        <title>Property Management System</title>
        <link rel="stylesheet" href="style.css">

    </head>
    <body class="auth-page">
  <form method="post" action="register.php">
                <div class="container">
            <h2>MAKAO Tenant Registration Portal</h2>
            <p>Enter the tenant information:</p>
            <input type="text" placeholder="First Name" name="first_name" required>
            <input type="text" placeholder="Last Name" name="last_name" required>
            <input type="text" placeholder="Phone Number" name="phone_number" required>   
            <input type="text" placeholder="ID Number" name="id_number" required>
            <input type="text" placeholder="Floor Number" name="floor_no" required>
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
            <input type="text" placeholder="Unit ID" name="unit_id" required>
            <input type="text" placeholder="block ID" name="block_id" required>
               <button type="submit">Register</button>
               <p class="small-note">Already registered? <a href="loginpage.php">Login here</a>.</p>
                </div>
 </form>
        <!-- <script src="script.js"></script> -->
    </body>
</html>