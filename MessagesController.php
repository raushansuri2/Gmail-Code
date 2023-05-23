<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller\Admin;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use App\Controller\Admin\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;

class MessagesController extends AppController{
	
	public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['welcomeemail', 'mailsend']);
    }
	
    public function index(){
		$this->loadModel('Users');
        $layoutTitle = 'Message';
        $this->set(compact('layoutTitle'));
        $this->viewBuilder()->layout('Admin/admin');
        $this->paginate = [
						   'limit' => 50,
						   'contain' => ['Users'],
						   'order' => ['Messages.created' => 'desc'],
						   'sortWhitelist' => ['Users.fullName','Users.email','Users.lastName','Users.middleName','Messages.created'],
						];
		$condition[] ='';
		$keyword = $this->request->query('keyword');
		$user_id = $this->request->query('userId');
		//if($user_id !=''){
		//	$condition[] = ['OR' => ['Messages.userId' => $user_id]];
		//}
		if($keyword != ''){
			$condition[] = [
				'OR' => [
					["Messages.userId" => $user_id],
					["Users.fullName LIKE '%".$keyword."%'"],
					["Users.email LIKE '%".$keyword."%'"],
					["Users.lastName LIKE '%".$keyword."%'"],
					["Users.middleName LIKE '%".$keyword."%'"],
					["Users.role LIKE '%".$keyword."%'"],
					["Users.contactNumber LIKE '%".$keyword."%'"]
				]				
			];
		}
		
		$query = $this->Messages->find('all')->where($condition);       
        
		$pages = $this->paginate($query);
		$this->set('pages', $pages);
		$user_listing=$this->Users->find()->where(['Users.role'=>'Member']);
		$this->set('magazines',$user_listing);
		
    }
				
				
	public function add($id=null){
		
		$this->loadModel('Users');
        $layoutTitle = 'Message-add';
        $this->set(compact('layoutTitle'));
        $this->viewBuilder()->layout('Admin/admin');
        $user = $this->Messages->newEntity();
		
								$this->set('user_dinamic',$id);
								$user_listing = $this->Users->find('list', ['keyField' => 'id', 'valueField' => function ($row) {
            return $row['email'] . ' (' .$row['fullName'] . ' ' . $row['middleName'].' '.$row['lastName'].')';
        }])
						->where(['Users.role'=>'Member', 'Users.id <>'=>'1', 'Users.status'=>'1'])
                        ->order(['Users.email'=>'ASC'])->toArray();
						//echo "<pre>";
						//print_r($user_listing); //die;
						//$user_listing = str_re
						//echo count($user_listing); die;
						//if($id !=''){
						//	$user->userId = ['0' => $id];
						//}
		if ($this->request->is('post')) {
					//echo "<pre>";
					//print_r($user_listing);
					//print_r($this->request->data); //die;
			$filepath='';
			   if($this->request->data['fileone']['name']!=''){
									$filename1=time() .'_'.$this->request->data['fileone']['name'];
									$path = WWW_ROOT . 'img/uploads/email/';	                    
									move_uploaded_file($this->request->data['fileone']['tmp_name'], $path.$filename1);
									$filepath[]=$path.$filename1;	
			   }
						if($this->request->data['filetwo']['name']!=''){
									$filename2=time() .'_'.$this->request->data['filetwo']['name'];
									$path = WWW_ROOT . 'img/uploads/email/';	                    
									move_uploaded_file($this->request->data['filetwo']['tmp_name'], $path.$filename2);
									$filepath[]=$path.$filename2;
						}
		   
			   if($this->request->data['filethr']['name']!=''){
									$filename3=time() .'_'.$this->request->data['filethr']['name'];
									$path = WWW_ROOT . 'img/uploads/email/';	                    
									move_uploaded_file($this->request->data['filethr']['tmp_name'], $path.$filename3);
									$filepath[]=$path.$filename3;
						}
					if($this->request->data['all'] == 1){
							foreach($user_listing as $key => $val){
										$this->request->data['userId'] = $key;
										$data['userId']=$this->request->data['userId'];
										$data['page_title']=$this->request->data['subject'];
										$data['content']=$this->request->data['messages'];
										$data['imageone']=$filename1;
										$data['imgtwo']=$filename2;
										$data['imgthr']=$filename3;
										$users = $this->Messages->newEntity(); 
										$users = $this->Messages->patchEntity($users, $data);
										$users['created'] = date('Y-m-d H:i:s'); 
										$this->Messages->save($users);
										$users_for_mails=$this->Users->find()->where(['Users.id'=>$this->request->data['userId']])->first();
										$data['name']=$users_for_mails->fullName;
										
										if($filepath){
											
												$file_name = $filename1;
												$file = $path.$filename1;
												$email = new Email('Newsmtp');
												$email->template('messages', 'default')
												->emailFormat('html')
												->viewVars(['email_data'=>$data])		
												->subject($data['page_title'].' : FreedomGiving')
												->to($users_for_mails->email)
												->attachments($path.$filename1)
												->from(['freedomgiving@gmail.com'=>'FreedomGiving'])
												->send();
										}else{
													$email = new Email('Newsmtp');
													$email->template('messages', 'default')
														->emailFormat('html')
														->viewVars(['email_data'=>$data])		
														->subject($data['page_title'].' : FreedomGiving')
														->to($users_for_mails->email)
														->from(['freedomgiving@gmail.com'=>'FreedomGiving'])
														->send();
													
										}
										
							}
					}else{
								foreach($this->request->data['userId'] as $val){
													//echo $val; die;
												$this->request->data['userId'] = $val;
												$data['userId']		=$val;
												$data['page_title']	=$this->request->data['subject'];
												$data['content']	=$this->request->data['messages'];
												$data['imageone']	=$filename1;
												$data['imgtwo']		=$filename2;
												$data['imgthr']		=$filename3;
												$users = $this->Messages->newEntity(); 
												$users = $this->Messages->patchEntity($users, $data);
												$users['created'] = date('Y-m-d H:i:s'); 
												$this->Messages->save($users);
												$users_for_mails=$this->Users->find()->where(['Users.id'=>$val])->first();
												//echo $users_for_mails->email; die;
												$data['name']=$users_for_mails->fullName;
												
													$subject = $data['page_title'];
													
												if($filepath){
															
															$file_name = $filename1;
															$file = $path.$filename1;
															$email = new Email('Newsmtp');
															$email->template('messages', 'default')
																	->emailFormat('html')
																	->viewVars(['email_data'=>$data])		
																	->subject($data['page_title'].' : FreedomGiving')
																	->to($users_for_mails->email)
																	->attachments($path.$filename1)
																	->from(['freedomgiving@gmail.com'=>'FreedomGiving'])
																	->send();							
												}else{
																$email = new Email('Newsmtp');
																$email->template('messages', 'default')
																	->emailFormat('html')
																	->viewVars(['email_data'=>$data])		
																	->subject($data['page_title'].' : FreedomGiving')
																	->to($users_for_mails->email)
																	->from(['freedomgiving@gmail.com'=>'FreedomGiving'])
																	->send();
												}
								}
					}
					$this->Flash->success(__('The Messages has been sent.'));
					return $this->redirect(['action' => 'index']);
			
			}
		
		
							$this->set('user_listing',$user_listing);
        $this->set('user', $user);
    }
    
    
    
    public function edit($id)
     {
        $layoutTitle = 'Messages-edit';
        $this->set(compact('layoutTitle'));
        $this->viewBuilder()->layout('Admin/admin');
        $pages = $this->Messages->get($id, ['contain' => []]);//fields name in contain[] with coma
        if ($this->request->is(['patch', 'post', 'put'])) {
            $pages = $this->Messages->patchEntity($pages, $this->request->data);
            if ($this->Messages->save($pages)) {
                $this->Flash->success(__('The Messages has been sent.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The Messages could not be send. Please, try again.'));
            }
        }
        $this->set(compact('pages'));
    }
    public function details($id = null) {
        $layoutTitle = 'Message';
        $this->set(compact('layoutTitle'));
        $this->viewBuilder()->layout('Admin/admin');
		$messages=$this->Messages->find()->where(['Messages.id'=>$id])->contain(['Users'])->first();
		$this->set('messages',$messages);
    }
	public function delete($id = null){
        $tag = $this->Messages->get($id);
        if ($this->Messages->delete($tag)) {
            $this->Flash->success(__('The Messages has been deleted.'));
        } else {
            $this->Flash->error(__('The Messages could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
	
	
	/*'Users.id <>'=>'1'*/
	function welcomeemail(){
		$this->viewBuilder()->layout('');
		$this->loadModel('Users');
		$this->loadModel('Welcomes');
		
		if($this->request->data){
			//echo "<pre>";
			//print_r($this->request->data);
			$ids = explode(",", $this->request->data['users']);
			//print_r($ids);
			//die('ddd');
			$this->paginate = [
										'limit' => '50',
										'order' => ['Users.id' => 'asc'],
										];
			
			$query = $this->Users->find('all')->where(['Users.role'=>'Member', 'Users.id IN'=>$ids, 'Users.status'=>'1']);
			
									$users = $this->paginate($query);
									$user_listing = $users->toArray();
					
					//print_r($user_listing);
					//die('ddd');				
			//$user_listing = $this->Users->find()->where(['Users.role'=>'Member', 'Users.id <>'=>'1', 'Users.status'=>'1'])->order(['Users.email'=>'ASC'])->toArray();
			//echo "<pre>"; print_r($user_listing); die;
			//foreach($user_listing as $gg){
			//	echo $gg->email."<br>";
			//}die;
			$p = "<table border='1px solid'><tbody>
			<tr>
			<td>ID</td>
				<td>First Name</td>
				<td>Middle Name</td>
				<td>Last Name</td>
				<td>Email</td>
				<td>Code</td>
				<td>Password</td>
			</tr>";
			
			foreach($user_listing as $key=>$val){
				
				
				$body ='<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>Freedom Giving</title>
					</head>
					<body>
						<table cellspacing="0" align="center" cellpadding="0" border="0" width="100%">
						
						
							<tr>
					<td>
									<h2 style=" font-size:20px; color:#50505a; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">
								Dear '.$val->fullName.',</h2>
									
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">Email: '.$val->email.'</p>
									
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; "><strong>Your information is OK and you are ready to donate.</strong></p>
									
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">Below is your <span style="color: red;"><strong>CODE</strong></span> and <span style="color: red;"><strong>PASSWORD</strong></span>. Keep this CODE in a Safe and Secure place. DO NOT SHARE YOUR CODE.</p>
									
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">1. After you Sign In, check the box at the bottom of the <span style="color: red;">Terms and Conditions</span> page acknowledging that
	you agree to the Terms and Conditions, this is to be done one time for the life of this Activity.</p>
									
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">2. Next, click on <span style="color: red;">E-Signature Page</span> and sign your name exactly as it appears on your deposit slip or Direct
	Deposit form sent to our office. This also will be done one time for the life of this Activity.</p>
									
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">3. Next, complete the "<span style="color: red;">Donating Statement</span>". This same "<span style="color: black;">Donating Statement</span>" must be signed every
	Monday by midnight without fail. You must click on "<span style="color: green;">Donating Statement</span>" then click on "<span style="color: green;">Submit New</span>"
	located in the upper right corner to see the "<span style="color: green;">Donating Statement</span>".</p>
									
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">You must follow the Donating Schedule located on your Dashboard, under "<span style="color: black;">Donations Sent</span>" to know
	the amount to fill in on your Donating Statement for the next week. Your first amount donated will be $25.00.</p>    
					
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; "><strong>Code: '.$val->uid.'</strong></p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; "><strong>Password: '.$val->password2.'</strong></p>
									<p>&nbsp;</p>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 0px; ">Go to <a href="https://www.freedomgiving.com">www.freedomgiving.com</a> and log in.</p>  
					</td>
	</tr>
							
							<tr>
								<td>
									<p style=" font-size:16px; color:#50505a; line-height:28px; font-family:Arial; font-weight:normal; margin:0; padding:0 0 25px; ">If you have any  problems, feel free to contact our support Team : <a href="mailto:freedomgiving@gmail.com" style="color:#4087c3; text-decoration:none;">
									freedomgiving@gmail.com </a> </p>
							
								</td>
							</tr>
					
							<tr>
								<td style="padding:25px 0; border-top:1px solid #e9e9ea;">
									<a href="#"><img src="https://www.freedomgiving.com/img/logo.png" width="150px" height="" /></a>
									<p style=" font-size:14px; color:#50505a; font-family:Arial; font-weight:normal; margin:10px 0 0;">Frredom Giving &copy; '.date('Y').'</p>
								</td>
							</tr>
						</table>
					</body>
				</html>';
				
				
				$subject = 'Welcome-FreedomGiving';
				$fromName = "FreedomGiving"; //
				//$fromEmail2 = "support@freedomgiving.com";
				$fromEmail2 = "freedomgiving@gmail.com";
				$to = 'xyz@evirtualservices.com';
				//$to = $val->email;
					//$body = $headder.$content.$footer; 
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					$headers .= 'From:  ' . $fromName . ' <' . $fromEmail2 .'>' . " \r\n" .
														'Reply-To: '.  $fromEmail2 . "\r\n" .
														"BCC: neha@mailinator.com, raushan.kumar@evirtualservices.com\r\n".
														'X-Mailer: PHP/' . phpversion();
					
					
					//$headers = "From: ". $fromName . ' <' . $fromEmail .'>' ."\r\n";
					//$headers .= "Reply-To: ".$fromEmail."\r\n";
					//$headers .= "Return-Path: ".$fromEmail."\r\n"; 
					//$headers .= "CC: sombodyelse@gmail.com\r\n";
					//$headers .= "BCC: rksuri2@gmail.com\r\n";
					////set content type to HTML 
					//$headers .= "Content-type: text/html\r\n";
	
					echo mail($to, $subject, $body, $headers);
				die('==A');
				$faqs = $this->Welcomes->newEntity();
				$DDDDD['userId'] 		= $val->id;
				$DDDDD['code'] 				= $val->uid;
				$DDDDD['email'] 			= $val->email;
				$DDDDD['name'] 				= $val->fullName;
				$DDDDD['message'] 	= $body;
				
				$faqs = $this->Welcomes->patchEntity($faqs, $DDDDD, ['validate' => false]);
				$this->Welcomes->save($faqs);
				
				$p .="<tr><td>".$val->id."</td>";
				$p .="<td>".$val->fullName."</td>";
				$p .="<td>".$val->middleName."</td>";
				$p .="<td>".$val->lastName."</td>";
				$p .="<td>".$val->email."</td>";
				$p .="<td>".$val->uid."</td>";
				$p .="<td>".$val->password2."</td>";
				//$email = new Email('default');
				//$email->template('welcome')
				//	->emailFormat('html')
				//	->viewVars(['email_data'=>$data])		
				//	->subject('Welcome-FreedomGiving')
				//	->to(trim($val->email))
				//	->from(['freedomgiving@gmail.com'=>'FreedomGiving'])
				//	->send();
			}
			//echo "<pre>";
			//print_r($email);
				
				
				$p .="</tbody></table>";
			echo $p;
			die('Welcome email sent successfully');
		}
	}
	
	
	function ree(){
		$this->loadModel('Users');
		$user_listing = $this->Users->find()
						->where(['Users.role'=>'Member', 'Users.id <>'=>'1', 'Users.status'=>'1'])
                        ->order(['Users.email'=>'ASC'])->toArray();
		foreach($user_listing as $val){
			$tablename = TableRegistry::get("Users");
				$query = $tablename->query();
				$result = $query->update()->set(['email' =>trim($val->email)])->where(['id' => $val->id])->execute();
		}
		die;
	}
	
	
	
	/*'Users.id <>'=>'1'*/
	function mailsend(){
		//die('sddsd');
		$this->viewBuilder()->layout('');
		$this->loadModel('Users');
		$this->loadModel('Welcomes');
		
		if($this->request->data){
			
			$ids = explode(",", $this->request->data['users']);
			$this->paginate = [
										'limit' => '50',
										'order' => ['Users.id' => 'asc'],
										];
			
					//$query = $this->Users->find('all')->where(['Users.role'=>'Member', 'Users.id IN'=>$ids, 'Users.status'=>'1']);
					$query = $this->Users->find('all')->where(['Users.role'=>'Member', 'Users.id <>'=>1, 'Users.status'=>'1']);
					//$users = $this->paginate($query);
					$user_listing = $query->toArray();
					//echo "<pre>";
					
					//print_r($user_listing); die;
					
			$p = "<table border='1px solid'><tbody>
			<tr>
			<td>ID</td>
				<td>First Name</td>
				<td>Middle Name</td>
				<td>Last Name</td>
				<td>Email</td>
				<td>Code</td>
				<td>Password</td>
			</tr>";
			
			foreach($user_listing as $key=>$val){
				
				$subject = 'Welcome-FreedomGiving';
				$fromName = "FreedomGiving"; //
				//$fromEmail2 = "support@freedomgiving.com";
				$fromEmail2 = "freedomgiving@gmail.com";
				$to = 'xyz@evirtualservices.com';
				//$to = $val->email;
					//$body = $headder.$content.$footer; 
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					$headers .= 'From:  ' . $fromName . ' <' . $fromEmail2 .'>' . " \r\n" .
														'Reply-To: '.  $fromEmail2 . "\r\n" .
														"BCC: neha@mailinator.com, raushan.kumar@evirtualservices.com\r\n".
														'X-Mailer: PHP/' . phpversion();
					
				
				$faqs = $this->Welcomes->newEntity();
				$DDDDD['userId'] 		= $val->id;
				$DDDDD['code'] 				= $val->uid;
				$DDDDD['email'] 			= $val->email;
				$DDDDD['name'] 				= $val->fullName;
				$DDDDD['password'] = $val->password2;
				//$body;
				
				$email = new Email('Newsmtp');
				$email->template('welcome')
					->emailFormat('html')
					->viewVars(['email_data'=>$DDDDD])		
					->subject('Welcome-FreedomGiving')
					->to($val->email)
					->from(['freedomgiving@gmail.com'=>'FreedomGiving'])
					->bcc(['freedomgiving@gmail.com', 'neha.yadav@evirtualservices.com'])
					->send();
					
		
			$DDDDD['message'] 	= '';
			//echo "<pre>"; print_r($email); 
					//echo mail($to, $subject, $body, $headers);
				//die('==A');
				
				
				$faqs = $this->Welcomes->patchEntity($faqs, $DDDDD, ['validate' => false]);
				$this->Welcomes->save($faqs);
				
				$p .="<tr><td>".$val->id."</td>";
				$p .="<td>".$val->fullName."</td>";
				$p .="<td>".$val->middleName."</td>";
				$p .="<td>".$val->lastName."</td>";
				$p .="<td>".$val->email."</td>";
				$p .="<td>".$val->uid."</td>";
				$p .="<td>".$val->password2."</td>";
				
			}
			//echo "<pre>";
			//print_r($email);
				
				
				$p .="</tbody></table>";
			echo $p;
			die('Welcome email sent successfully');
		}
	}
	
					function gmaillogin(){
									$layoutTitle = 'Gmail-login';
									$this->set(compact('layoutTitle'));
									$this->viewBuilder()->layout('Admin/admin');
									if ($this->request->is(['patch', 'post', 'put'])) {
													//echo "<pre>";
													//print_r($this->request->data); 
													$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
													$username = $this->request->data('username');
													$password = $this->request->data('password');
													$inbox = imap_open($hostname,$username,$password);
													$emails = imap_search($inbox,"ALL");
													//print_r($this->request->data); die;
													if ($emails) {
														$Gmail['GMAIL.username'] = $this->request->data('username');
														$Gmail['GMAIL.password'] = $this->request->data('password');
																$this->request->session()->write($Gmail);
																$this->Flash->success(__('Login successfully.'));
																return $this->redirect(['action' => 'gmailinbox']);
													} else {
																	$this->Flash->error(__('Cannot connect to Gmail: ' . imap_last_error()));
													}
									}
									$this->set(compact('pages'));
						}
					
					
					function gmailinbox(){
								$layoutTitle = 'Gmail-login';
								$this->set(compact('layoutTitle'));
								$this->viewBuilder()->layout('Admin/admin');
								
								$GG = $this->request->session()->read($Gmail);
								if($GG['GMAIL']['username'] == ''){
											$this->Flash->error(__('Gmail credentials is invalid.'));
											return $this->redirect(['action' => 'gmaillogin']);
								}
								
								$page = 1;
								if($this->request->query('page') != ''){
									$page = $this->request->query('page');
								}
								$page = $page-1;
								
								$split_Array = ''; 
								$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
								$username = $GG['GMAIL']['username'];
								$password = $GG['GMAIL']['password'];
								$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
								$emails = imap_search($inbox,"ALL");
								if($emails) {
										rsort($emails);
										$total_Array= array_chunk($emails,25);
										$split_Array= $total_Array[$page];
								}else{
											$this->Flash->error(__('Gmail credentials is invalid.'));
											return $this->redirect(['action' => 'gmaillogin']);
								}
								$this->set(compact('total_Array','split_Array', 'hostname', 'username', 'password'));
					}
					
					function gmailread($email_number=NULL){
								$layoutTitle = 'Gmail-login';
								$this->set(compact('layoutTitle'));
								$this->viewBuilder()->layout('Admin/admin');
								
								$GG = $this->request->session()->read($Gmail);
								if($GG['GMAIL']['username'] == ''){
											$this->Flash->error(__('Gmail credentials is invalid.'));
											return $this->redirect(['action' => 'gmaillogin']);
								}
								
								$page = 1;
								if($this->request->query('page') != ''){
									$page = $this->request->query('page');
								}
								$page = $page-1;
								
								$split_Array = ''; 
								$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
								$username = $GG['GMAIL']['username'];
								$password = $GG['GMAIL']['password'];
								$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
								$emails = imap_search($inbox,"ALL");
								if($emails) {
										
								}else{
											$this->Flash->error(__('Gmail credentials is invalid.'));
											return $this->redirect(['action' => 'gmaillogin']);
								}
								$this->set(compact('hostname', 'username', 'password', 'email_number'));
					}
					
					function logout(){
							$this->request->session()->delete('GMAIL');
       $this->Flash->success(__('Gmail account logout successfully.'));
								return $this->redirect(['action' => 'gmaillogin']);
					}
}