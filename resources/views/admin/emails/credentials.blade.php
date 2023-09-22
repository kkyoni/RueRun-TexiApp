<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{{$title}}</title>
	<style>
		*{ margin:0; padding: 0; }
		body{ background: #fff; margin: 0; padding: 0; font-family: 'Arial'; }
		
		@media only screen and (max-width: 640px)  {
			body[yahoo] .deviceWidth {width:100%!important; padding:0; }	
			body[yahoo] .center {text-align: center!important;}
			body[yahoo] .banners {width:100% !important;}
		}

		@media only screen and (max-width: 479px) {
			body[yahoo] .deviceWidth {width:100%!important; padding:0; }	
		}
		
	</style>
</head>
<body yahoo="fix">
	<table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" align="center">
		<tr> 
			<td width="100%" align="center">
				<table width="650" bgcolor="#F6F6F6" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
					<tr>
						<td align="center" style="padding: 10px 0;"><img src="http://callcenter.aistechnolabs.co/assets/front/dist/img/logo.png" height="50px" width="250px" alt="Call center"></td>

					</tr>
					<tr>
						<td style="font-family:Helvetica, Arial, sans-serif; font-size: 14px; color: #585858; padding: 20px;line-height:150%;">Dear {{ucfirst($user)}} ,<br>
							<!-- <br>
							We’ve added as a {{$user_type}}.
							<br> -->
							<br>
							You can login through this link: <a href="{{$link}}">Click Here</a>
							<br>
							Email: {{$email}}<br> 
							Password: {{$password}}  
						</td>
					</tr>				
					<tr>
						<td style="font-family:Helvetica, Arial, sans-serif; font-size: 14px; color: #585858; padding: 20px;line-height:150%;">If you didn’t make the request, just ignore this message.<br><br>Regards,<br>
							{{\Settings::get('project_title')}}</td>
						</tr>
						<tr>
							<td style="border-bottom: 1px #cecece solid;">&nbsp;</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>
	</body>
	</html>