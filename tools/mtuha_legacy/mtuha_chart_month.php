<?php require_once('header.php'); ?>
<!-- page content -->
<div class="right_col" role="main">
  <!-- top tiles -->
  <div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="dashboard_graph">
        <div class="row x_title">
          <div class="col-md-6">
            <h3>MTUHA
              <form action="" method="post">
                <select name="mwaka">
                  <?php
                  if (isset($_POST['mwaka'])) {
                    echo '<option value="' . $_POST['mwaka'] . '">' . $_POST['mwaka'] . ' </option>';
                  }
                  $result = $conn->query("SELECT DISTINCT YEAR(createdon) as mwaka FROM patients ORDER BY createdon DESC");
                  while ($row = mysqli_fetch_array($result)) {
                    echo '<option value="' . $row['mwaka'] . '">' . $row['mwaka'] . ' </option>';
                  }
                  ?>
                </select>
                <select name="mwezi">
                  <?php
                  if (isset($_POST['mwezi'])) {
                    $month_name = date("F", mktime(0, 0, 0, $_POST['mwezi'], 10));
                    echo '<option value="' . $_POST['mwezi'] . '">' . $month_name . ' </option>';
                  }
                  $result = $conn->query("SELECT DISTINCT MONTH(createdon) as mwezi, MONTHNAME(createdon) as jina FROM patients ORDER BY createdon DESC");
                  while ($row = mysqli_fetch_array($result)) {
                    echo '<option value="' . $row['mwezi'] . '">' . $row['jina'] . ' </option>';
                  }
                  ?>
                </select>
                <button name="search" value="Search">Show</button>
              </form>
              <?php if (isset($_POST['mwaka'])) { ?>
                <form action="data_print.php" method="post" target="_blank">
                  <input type="hidden" value="<?php echo $_POST['mwaka']; ?>" name="mwakam" />
                  <input type="hidden" value="<?php echo $_POST['mwezi']; ?>" name="mwezim" />
                  <button type="submit" name="print_mtuha">Print</button>
                </form>
            </h3>
          </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
          <?php require_once('mtuha_months.php'); ?>
        </div>
        <?php } ?>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
  <br />
</div>
<!-- /page content -->
<?php require_once('footer.php'); ?>
