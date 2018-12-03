<?php 
  session_start(); 

  if (!isset($_SESSION['username'])) {
  	$_SESSION['msg'] = "You must log in first";
  	header('location: login.php');
  }
  if (isset($_GET['logout'])) {
  	session_destroy();
  	unset($_SESSION['username']);
  	header("location: login.php");
  }
  
  
	if(isset($_POST['GetLeave'])) {
		$_SESSION['lateLeave']++;
		$db = mysqli_connect('localhost', 'root', '', 'registration');
		$sql = "UPDATE users SET lateLeave = ".$_SESSION['lateLeave']." Where id = ".$_SESSION['id'];
		mysqli_query($db, $sql);
		$datemonth = date('MY');
		$id = $_SESSION['id'];
		if ($_SESSION['lateLeave'] == 1) {
			
			$sql = <<<EOD
				INSERT into leaverecord (id, monthyear,counter,l1Date) values ($id,'$datemonth',1,CURDATE())
EOD;
			} else if  ($_SESSION['lateLeave'] == 2) { 
				$sql = <<<EOD
				UPDATE leaverecord set l2Date= CURDATE(), counter = 2 where id = $id and monthyear = '$datemonth';
EOD;
		} else if  ($_SESSION['lateLeave'] == 3){
			$sql = <<<EOD
			UPDATE leaverecord set l3Date= CURDATE(), counter = 3 where id = $id and monthyear = '$datemonth';
EOD;
		}
		mysqli_query($db, $sql);
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	<link rel="stylesheet" type="text/css" href="style.css">
	<script>
function myFunction() {
  // Declare variables 
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    } 
  }
}
</script>

</head>
<body>

<div class="header">
	<h2>Home Page</h2>
</div>
<div class="content">
  	<!-- notification message -->
  	<?php if (isset($_SESSION['success'])) : ?>
      <div class="error success" >
      	<h3>
          <?php 
          	echo $_SESSION['success']; 
          	unset($_SESSION['success']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>

    <!-- logged in user information -->
    <?php  if (isset($_SESSION['username'])) : ?>
		<?php
		if ($_SESSION['username'] == "admin") {?>
			<input type="text" style="width:40%"id="myInput" onkeyup="myFunction()" placeholder="Search with Ids">
			<br/>
			<table id="myTable" border>
			<tr class="header">
				<th style="width:35%;">SID</th>
				<th style="width:20%;">Username</th>
				<th style="width:20%;">Date</th>
				<th style="width:20%;">Late</th>
			</tr>
			<?php 
				$db = mysqli_connect('localhost', 'root', '', 'registration');
				$SQ = <<<EOD
					SELECT * FROM leaverecord
EOD;
				$r = mysqli_query($db, $SQ);
				$data = array();
				while($row = mysqli_fetch_assoc($r)) {
					$id = $row['id'];
					$sql = "select username from users where id = $id";
					$res = mysqli_query($db, $sql);
					$name = "";
					while($roo = mysqli_fetch_array($res)) {
						$name = $roo['username'];
					}
					$late= "";
					if($row['counter'] > 0) {
						if($row['l1late'] == 0)
							$late = "No";
						else
							$late = "Yes";
						$data[] = array('id' => $row['id'], 'date' => $row['l1date'],'name' => $name, 'late'=>$late);
					} 
					if($row['counter'] > 1) {
						if($row['l2late'] == 0)
							$late = "No";
						else
							$late = "Yes";
						$data[] = array('id' => $row['id'], 'date' => $row['l2date'],'name' => $name,'late'=>$late);
					}
					if($row['counter'] > 2) {
						if($row['l3late'] == 0)
							$late = "No";
						else
							$late = "Yes";
						$data[] = array('id' => $row['id'], 'date' => $row['l3date'],'name' => $name,'late'=>$late);
					}
				}
				usort($data, function ($a, $b) {
					$dt1  = new DateTime($a['date']);
					$dt2   = new DateTime($b['date']);
					return $dt1->getTimestamp() - $dt2->getTimestamp();
				});
				//print_r($data);
				foreach($data as $key => $value) {
					$id = $value['id'];
					$name = $value['name'];
					$date = $value['date'];
					$late = $value['late'];
					$html = <<<EOD
					<tr>
					<td>$id</td>
					<td>$name</td>
					<td>$date</td>
					<td>$late</td>
					</tr>
EOD;
					echo $html;
				}
			?>
			
		
			</table>
			<p> <a href="index.php?logout='1'" style="color: red;">logout</a> </p>

		<?php
		} else { 
		?>
		<p>Welcome <strong><?php echo $_SESSION['username']; ?></strong></p>
		<p>Student ID: <strong><?php echo $_SESSION['sid']; ?></strong></p>
		<p>Email Address: <strong><?php echo $_SESSION['email']; ?></strong></p>
		<p>Gender: <strong><?php if($_SESSION['gender'] == 1) echo "Male"; else echo "Female";?></strong></p>
		<p>LateLeave: <strong><?php echo $_SESSION['lateLeave']; ?></strong></p>
		
		<?php 
		
		$db = mysqli_connect('localhost', 'root', '', 'registration');
		$id = $_SESSION['id'];
		$SQ = <<<EOD
		SELECT * FROM leaverecord where id = $id and (l1date = CURDATE() or l2date = CURDATE() or l3date = CURDATE() )
EOD;
		$res = mysqli_query($db, $SQ);
		if (mysqli_num_rows($res) <= 0 && $_SESSION['lateLeave'] < 3 && date("Hi") < "2230" && date("Hi") > "0900") { ?>  
			<form name = "getleave" action = index.php method = 'post'>
			<p>Click Here to get a late leave:</p>
				<input type="submit" name = "GetLeave" value ="Get Leave"/>
			</form>
		<?php } else {?>
			<form name = "getleave" action = index.php method = 'post'>
			<p>Click Here to avail late leave:</p>
				<input type="submit" disabled=true name = "GetLeave" value ="Get Leave"/>
			</form>
		<?php } ?>
    	<p> <a href="index.php?logout='1'" style="color: red;">logout</a> </p>
		<?php }endif ?>
</div>
		
</body>
</html>