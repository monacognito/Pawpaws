<?php
    session_start();

    // if not logged in redirect to login
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) 
    {
        header("location: login.php");
        exit;
    }

    require_once 'controllers/connection.php';

    // Get members 
    $query_active_member = "select id, name, type, gender, owner_mobile, address, expired_at from members
                            where expired_at >= now();";
    $query_inactive_member = "select id, name, type, gender, owner_mobile, address, expired_at from members
                            where expired_at < now();";
    $result_active_member = mysqli_query($conn, $query_active_member);
    $result_inactive_member = mysqli_query($conn, $query_inactive_member);

    // New member form
    $member_submit = isset($_POST["newMemberSubmit"]) ? $_POST["newMemberSubmit"] : '';
    $submit_result = "";
    $error = "";

    if ($member_submit === "submit") 
    {
        $name = $type = $gender = $owner_mobile = $address = $error = "";

        // Check all
        $name = (empty(trim($_POST["name"])) ? "" : trim($_POST["name"]));
        $error = (empty(trim($_POST["name"])) ? "Name cannot be empty" : "");
        $type = (empty(trim($_POST["type"])) ? "" : trim($_POST["type"]));
        $error = (empty(trim($_POST["type"])) ? "Breed cannot be empty" : "");
        $gender = (isset($_POST["gender"]) ? (empty(trim($_POST["gender"])) ? "" : trim($_POST["gender"])) : "");
        $error = isset($_POST["gender"]) ? (empty(trim($_POST["gender"])) ? "Gender cannot be empty" : "") : "Gender cannot be empty";
        $owner_mobile = (empty(trim($_POST["owner_mobile"])) ? "" : trim($_POST["owner_mobile"]));
        $error = (empty(trim($_POST["owner_mobile"])) ? "Mobile cannot be empty" : "");
        $address = (empty(trim($_POST["address"])) ? "" : trim($_POST["address"]));
        $error = (empty(trim($_POST["address"])) ? "Address cannot be empty" : "");

        // All valid
        if (empty($error))
        {
            $query_new_member = "insert into members value ("
            . "default,\""
            . $name . "\",\""
            . $type . "\",\""
            . $gender . "\",\""
            . $owner_mobile . "\",\""
            . $address . "\","
            . "default,"
            . "default);";
            if (mysqli_query($conn, $query_new_member)) 
            {
                $submit_result = "Success adding member named " . $name;
                header("location: membership.php");
            } else
            {
                $submit_result = "An error occured.";
            }
            
        }
    }

    // Handle delete
    if (array_key_exists("deleteMember", $_POST))
    {
        handleDeleteMember($_POST["deleteMember"], $conn);
    }

    function handleDeleteMember($id_delete, $conn_delete)
    {
        $query_delete_member = "delete from members where id=".$id_delete.";";
        if (mysqli_query($conn_delete, $query_delete_member))
        {
            header("location: membership.php");
        } else
        {
            $error = "Cannot delete";
        }
    }

    // Handle extend
    if (array_key_exists("extendMember", $_POST)) 
    {
        handleExtendMember($_POST["extendMember"], $link);
    }

    function handleExtendMember($id_extend, $conn_extend)
    {
        $query_extend_member = "update members set expired_at = date_add(now(), interval 6 month) where id=".$id_extend.";";
        if (mysqli_query($conn_extend, $query_extend_member)) 
        {
            header("location: membership.php");
        } else 
        {
            $error = "Cannot extend"; 
        }
    } 

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/navbar.css">
  <link rel="stylesheet" href="./css/membership.css">
  <link rel="stylesheet" href="./css/main.css">
  <title>Membership</title>
</head>
<body>
  <div class="navbar-container">
    <div class="navbar-row">
      <div class="navbar-left cg-10px">
        <a href="dashboard.php" class="navbar-item">Dashboard</a>
        <a href="grooming.php" class="navbar-item">Grooming</a>
        <a href="purchase.php" class="navbar-item">Purchase</a>
        <a href="membership.php" class="navbar-item navbar-on">Membership</a>
        <div class="h-20px flex-row mt-10px">
          <input class="delete-button" style="height:30px" type="submit" name="" value="0">
          <div>-delete</div>
        </div>
        <div class="h-20px flex-row mt-10px">
          <input class="extend-button" style="height:30px" type="submit" name="" value="0">
          <div>-mark as paid</div>
        </div>
      </div>
      <a href="logout.php" class="navbar-item">Logout</a>
    </div>
  </div>

  <div class="flex">
    <div class="flex-20 padding-10px center-child-horizontal overflow-auto h-100">
      <div class="container-new-member">
        <?php echo $submit_result; ?>
        <?php echo $error; ?>
        <form action="membership.php" method ="post">
          <h2>New Member</h2>
          <label>name</label>
          <input class="block" required="required" type="text" name="name" maxlength="50" placeholder="Bonne">
          <label>Animal Breed</label>
          <input class="block" required="required"  type="text" name="type" maxlength="50" placeholder="Chihuahua Dog">
          <fieldset>
            <legend>gender</legend>
            <input type="radio" name="gender" checked="checked" value="m" id="gender_m">
            <label for="gender_m">Male</label>
            <input type="radio" name="gender" value="f" id="gender_f">
            <label for="gender_f">Female</label>
          </fieldset>
          <label>Owner's mobile</label>
          <input class="block" required="required" type="text" name="owner_mobile" maxlength="14" placeholder="085320002000">
          <label>Address</label>
          <input class="block" required="required" type="text" name="address" maxlength="50" placeholder="9 Blue Ave. Cimahi">
          <input type="submit" name="newMemberSubmit" value="submit">
        </form>
      </div>
    </div>
    <div class="flex-30 padding-10px overflow-auto h-100">
      <h2>Active Member</h2>
      <ul>
        <?php
        if (mysqli_num_rows($result_active_member)) {
          $sn = 1;
          while ($data = mysqli_fetch_assoc($result_active_member)) {
            ?>
              <li class="flex-col">
                <div class="flex-row justify-between">
                  <div>(ID: <?php echo $data['id']; ?>)
                  <b><?php echo $data['name']; ?></b> - <?php echo $data['type']; ?> 
                  </div>
                  <form method="post" style="display:inline;">
                    <input class="delete-button" type="submit" name="deleteMember" value=<?php echo $data['id']?>>
                  </form>
                </div>
                <div>Expiry Date <?php echo $data['expired_at']; ?> </div>
              </li>  
            <?php $sn++;
          }
        } else { ?>
            <tr>
              <div colspan="8">No data found</div>
            </tr>
        <?php } ?>
      </ul>
    </div>
    <div class="flex-30 padding-10px overflow-auto h-100">
      <h2>Expired Member</h2>
        <ul>
          <?php
          if (mysqli_num_rows($result_inactive_member)) {
            $sn = 1;
            while ($data = mysqli_fetch_assoc($result_inactive_member)) {
              ?>
                <li class="flex-col">
                  <div class="flex-row justify-between">
                    <div>(ID: <?php echo $data['id']; ?>)
                      <b><?php echo $data['name']; ?></b> - <?php echo $data['type']; ?> 
                    </div>
                    <div>
                      <form method="post" style="display:inline;">
                        <input class="delete-button" type="submit" name="deleteMember" value=<?php echo $data['id']?>>
                      </form>
                      <form method="post" style="display:inline;">
                        <input class="extend-button" type="submit" name="extendMember" value=<?php echo $data['id']?>>
                      </form>
                    </div>
                  </div>
                  <div>Expiry Date: <?php echo $data['expired_at']; ?> </div>
                </li>  
              <?php $sn++;
            }
          } else { ?>
              <tr>
                <div colspan="8">No data found</div>
              </tr>
          <?php } ?>
        </ul>
      </div>
    </div>
    
  </body>
</html>
