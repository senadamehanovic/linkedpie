<html>
<head>
     <title>User Login And Registration</title>
     <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <div class="container">
    <div class="login-box">
    <div class="row">
    <div class="col-md-6 login-left">
       <h2>Login Here</h2>
       <Form action="validation.php" method="post">
         <div class="form-group">
         <label for="Username"></label>
         <input type="text" name="user" class="form-control" required>
         </div>
         <div class="form-group">
         <label for="Password"></label>
         <input type="password" name="password" class="form-control" required>
         </div>
         <button type="submit" class="btn btn-primary"> Login</button>
    </Form>
    </div>

    <div class="col-md-6 login-right">
       <h2>Register Here</h2>
       <Form action="registration.php" method="post">
         <div class="form-group">
         <label for="Username"></label>
         <input type="text" name="user" class="form-control" required>
         </div>
         <div class="form-group">
         <label for="Password"></label>
         <input type="password" name="password" class="form-control" required>
         </div>
         <button type="submit" class="btn btn-primary"> Register</button>
    </Form>
    
    </div>
    </div>
    </div>
</body>

</html>