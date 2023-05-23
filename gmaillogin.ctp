<?php 
$pagingDiv = false;//($this->Paginator->counter('{:pages}') > 1)? true :false;
$pagingDiv = $this->Paginator->templates('number');
?>

<div class="mainwrapper">
	<div class="leftpanel">
		 <?php echo $this->element('admin/sidebar');?>             
	</div><!-- leftpanel -->
	
	<div class="mainpanel">
		<div class="pageheader">
			<div class="media">
				<div class="pageicon pull-left">
					<i class="fa fa-hdd-o"></i>
				</div>
				<div class="media-body" style="width: 20%">
					<ul class="breadcrumb">
						<li><a href="<?php echo $this->Url->build(['controller' => 'admins', 'action' => 'dashboard']);?>"><i class="glyphicon glyphicon-home"></i> Dashboard</a></li>
						<li>Gmail Inbox</li>
					</ul>
					<h4>Gmail Inbox</h4>
				</div>
				<div class="search-body" style="width: 45%">
					<a style="float: right;" href="<?php echo $this->Url->build(['controller' => 'Messages', 'action' => 'add']);?>" title="Send Mail" class="btn btn-primary mr5 ml10" >Send Mail</a>
				<?php 
					echo $this->Form->create('Departments',  ['type' => 'get', 'novalidate' => 'novalidate']);
				//	echo $this->Form->input('userId',['type' => 'select', 'multiple' => false,'options' => $magazines, 'div'=>false, 'label'=>false,  'empty' => true, 'class'=>'form-control width200']);
					?>
					 
					 <!--<select name="userId" class="form-control width200" id="userid">
					 <option value="">Select User</option>
					 <?php foreach($magazines as $value){ ?>
					 <option  <?php if($_GET['userId']==$value->id){ echo 'selected'; } ?> value="<?php echo $value->id; ?>">
					 <?php echo $value->email; ?></option>
					 <?php } ?>
					 </select>-->
    <?php
				 echo $this->Form->input('keyword', ['templates' => ['inputContainer' => '{{content}}'],'value'=>$this->request->query('keyword'), 'class'=>'form-control width200','placeholder'=>'Enter Keyword to Search', 'style' => 'float:left', 'div'=>false, 'label'=>false, 'autocomplete'=>'off']);
   
                    $this->Form->templates(['submitContainer' => '{{content}}']);                    
                    echo $this->Form->submit('Search', ['class' => 'btn btn-primary mr5 ml10',  'div' => false, 'label' =>false]);
					
					echo $this->Form->end();
				?>
									 

				</div>
				
			</div><!-- media -->
		</div><!-- pageheader -->
		
		<div class="contentpanel">      
			<?php echo $this->Flash->render(); ?>
			<div class="paging-container">
						<p></p>
						<p class="records-showing">Showing 1 - 50 of 628 Members</p>
						<p></p>
      <ul>
							<?php $pagee = $this->request->query('page');
													if($pagee ==''){
														$pagee = 1;
													}
													$start = 1;
													if($pagee > 8){
														$start = $pagee-5;
													}
													$End = $start+9;
													if($End > count($total_Array) || count($total_Array) < 9){
														$End = count($total_Array);
													}
								?>
							
									<li class="prev disabled"><a href="" onclick="return false;">Previous</a></li>
									<?php $class = '';
									for($p=$start; $p<=$End; $p++){
									//foreach($total_Array as $key=>$ARR){
											$current = $p;
											$class = '';
												if($pagee ==  $current){
														$class = ' class="active"';
												}
												?>
										<li<?php echo $class;?>><a href="<?php echo $this->Url->build(['controller' => 'messages', 'action' => 'gmailinbox?page='.$current]);?>"><?php echo $current;?></a></li>
									<?php } ?>
								
									<li class="next disabled"><a rel="next" href="/admin/users/all?page=2">Next</a></li>
									
									
									
						</ul>
      <div class="cl"></div>
			</div>
			<div class="panel panel-primary-head"> 
				<table id="basicTable" class="table table-striped table-bordered responsive">
					                        
					<tbody>									
						<?php
						
								$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
								$i = 1;
								//pr($split_Array); die('QQQ');
								if(count($split_Array)){
									foreach ($split_Array as $email_number):
									
									$headerInfo = imap_headerinfo($inbox,$email_number);
									$structure = imap_fetchstructure($inbox, $email_number);
									$overview = imap_fetch_overview($inbox,$email_number,0);
								?>
								<tr>
										<td><?php echo $overview[0]->from."(".$headerInfo->from[0]->mailbox."@".$headerInfo->from[0]->host.")"; ?></td>
										<td><strong><?php echo  $overview[0]->subject; ?></strong></td>
								</tr>
								<?php
								$i++;
							endforeach;
						}else {
							echo "<tr><td colspan='2' class='error'>No Record Found...</td></tr>";
						}
                        ?> 
					</tbody>
				</table>
			</div><!-- panel -->                  
			
			
			<div class="paging-container">
						<p></p>
						<p class="records-showing">Showing 1 - 50 of 628 Members</p>
						<p></p>
      <ul>
									<li class="prev disabled"><a href="" onclick="return false;">Previous</a></li>
									<li class="active"><a href="">1</a></li>
									<li><a href="/admin/users/all?page=2">2</a></li>
									<li><a href="/admin/users/all?page=3">3</a></li>
									<li><a href="/admin/users/all?page=4">4</a></li>
									<li><a href="/admin/users/all?page=5">5</a></li>
									<li><a href="/admin/users/all?page=6">6</a></li>
									<li><a href="/admin/users/all?page=7">7</a></li>
									<li><a href="/admin/users/all?page=8">8</a></li>
									<li><a href="/admin/users/all?page=9">9</a></li>
									<li class="next"><a rel="next" href="/admin/users/all?page=2">Next</a></li>
						</ul>
      <div class="cl"></div>
			</div>
			
		</div><!-- contentpanel -->
			
	</div><!-- mainpanel -->
</div><!-- mainwrapper -->        

<style>
.media .form-control {
 display: inline-block !important;

 margin-left: 15px !important;
}
</style>