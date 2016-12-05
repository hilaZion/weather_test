<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Weather Test</title>
        <script src="js/libs/jquery/jquery.min.js" type="text/javascript"></script>
		<script src="js/scripts.js" type="text/javascript"></script>
        <link href="css/bootstrap.css" rel="stylesheet" type="text/css"/>
		<link href="css/style.css" rel="stylesheet" type="text/css"/>
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">
                        Weather Test
                    </a>
                </div>
            </div>
        </nav>
        <div class="row">
            <div class="col-lg-12">
                <div class="container">
                    <div class="panel panel-default">
                        <div class="panel-body">
							<form action="#" method="POST" >
								<div class="input-group col-lg-6">	
									<input name="city" type="text" class="form-control" placeholder="City"/>
									<div class="input-group-btn">
										<input type="submit"  class="btn1 btn btn-primary" name="submit1" value="Get Current Forecast"/>
									</div>
									<input type="submit" name="submit2" value="Load Forecast from DB" class="btn2 btn btn-danger pull-right" />
								</div>
							</form>
                        </div>
                    </div>
<!--Div for dispalaying current forecast -->					
					<div>
					<?php
					 if (isset($_POST['submit1']))
					 {
						if(isset($_POST['city']))
						{ 
							$city = $_POST['city']; 
							$api_url = 'http://api.openweathermap.org/data/2.5/forecast?q='. $city .'&units=metric&appid=e4b8b08c185638b825af37facfe1fabb';
							$weather_data = @file_get_contents($api_url);
							if ($weather_data == TRUE)
							{
								$json = json_decode($weather_data, TRUE);
								$list_arr = $json['list'];
								$arrlength = count($list_arr);
								$city_name = $json['city']['name'];
								$period_start = reset($list_arr)['dt_txt'];
								$period_end = end($list_arr)['dt_txt'];
					?>
									<input type="submit" class="btn btn-success pull-right" name="submit3" value="Save Forecast to DB">
								<h3><?php echo $city_name ?></h3>
								<h4>Period: <?php echo $period_start ?> - <?php echo $period_end ?></h4> 
								<table class="table table-hover">
									<thead>
										<tr>
											<th>Date/Time</th>
											<th>Min Temp</th>
											<th>Max Temp</th>
											<th>Wind Speed</th>
										</tr>
									</thead>						
								<tbody>
								<?php		
									for ($x = 0; $x < $arrlength; $x++)
									{
										$user_time = $list_arr[$x]['dt_txt'];
										$min_temp = $list_arr[$x]['main']['temp_min'];
										$max_temp = $list_arr[$x]['main']['temp_max'];
										$wind_sp = $list_arr[$x]['wind']['speed'];	
										echo    "<tr>";
										echo	"<td>" . $user_time. "</td>";
										echo	"<td>" . $min_temp . "</td>";
										echo	"<td>" . $max_temp . "</td>";
										echo	"<td>" . $wind_sp . "</td>";
										echo    "<tr>";
									}
															
									//connect to the database
									$link = NEW MySQLi('localhost','root','','weather_test');
									if ($link->connect_error) { die("Connection failed: " . $link->connect_error);}
									$min_data = $list_arr[0]['main']['temp_min'];
									$max_data = $list_arr[0]['main']['temp_max'];
									$wind_sp_data = $list_arr[0]['wind']['speed'];
									$sql1 = "INSERT INTO city (c_city_name, created_at, updated_at) VALUES ('$city_name', '$period_start', '$period_start')";
									$sql2 = "INSERT INTO temp (t_temp_max, t_temp_min, t_date_time, t_city_id) VALUES ('$max_data', '$min_data', '$period_start', '10')"; 
									$sql3 = "INSERT INTO wind (w_speed, w_city_id) VALUES ('$wind_sp_data', '10')";
									if( (mysqli_query($link, $sql1)) && (mysqli_query($link, $sql2)) && (mysqli_query($link, $sql3)) )
									{
									echo "Records added successfully.";
									}
									else
									{
									echo "ERROR: Could not able to execute $sql1. " . mysqli_error($link);
									}
								
								
							 }
						}
					}
								?>	
                            </tbody>
                        </table>
                    </div>
<!--Div for dispalaying forecast from DB -->				
                    <div>
						<?php
 if (isset($_POST['submit2'])) 
 {
	if(isset($_POST['city']))
	{ 
			$city = $_POST['city'];
							//connect to the database
							$mysqli = NEW MySQLi('localhost','root','','weather_test');
							if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error);} 
							//Query the database
							$resultSet = $mysqli->query("SELECT c.c_city_name, c.updated_at, t_date_time, t_temp_max, t_temp_min, w.w_speed FROM temp as t INNER JOIN city as c ON t.t_city_id=c.c_id INNER JOIN wind w ON c.c_id=w.w_city_id WHERE c.c_city_name='$city';");
							$row = mysqli_fetch_array($resultSet);
							if($resultSet->num_rows !=0)
							{
								echo "<h3>". $row['c_city_name'] ."</h3>";
								echo "<h4>update at: ". $row['updated_at'] ."</h4>";	
						?>
								<table class="table table-hover">
									<thead>
										<tr>
											<th>Date/Time</th>
											<th>Min Temp</th>
											<th>Max Temp</th>
											<th>Wind Speed</th>
											</tr>
									</thead>						
									<tbody>
									<?php
										echo    "<tr>";
										echo	"<td>" . $row['t_date_time']. "</td>";
										echo	"<td>" . $row['t_temp_min'] . "</td>";
										echo	"<td>" . $row['t_temp_max'] . "</td>";
										echo	"<td>" . $row['w_speed'] . "</td>";
										echo    "<tr>";
							}
							$mysqli->close();
			}
}
									 ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
