<?php // This page is for managing all the forum replies

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
$table_name = $wpdb->prefix . 'forum_topic_reply';

$replies = $wpdb->get_results("SELECT * FROM $table_name");

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
          </div><!-- /.box-header -->
          <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Reply</th>
                  <th>User</th>
                  <th>Topic</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($replies as $reply) { ?>
                <tr>
                  <td><?php echo $reply->id; ?></td>
                  <td><?php echo $reply->replies; ?></td>
                  <td><?php echo $reply->user_id; ?></td>
                  <td><?php echo $reply->topic_id; ?></td>
                  <td>
                    <form action="" method="POST">
                      <input type="hidden" value="<?php echo $reply->id; ?>" name="reply_id" >
                      <input type="submit" name="edit_reply" value="Edit" >
                      <input type="submit" name="edit_reply" value="Delete" >
                    </form>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Reply</th>
                  <th>User</th>
                  <th>Topic</th>
                  <th>Action</th>
                </tr>
              </tfoot>
            </table>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
  $(function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });

  initSample();
</script>
