<?php defined('_JEXEC') or die;

if(isset($this->message)): ?>
	<dl id="system-message">
		<dt class="message">Message</dt>
		<dd class="message fade">
			<ul><?php echo $this->message;?> </ul>
		</dd>
	</dl>
<?php endif;

if(isset($this->error)) :?>
	<dl id="system-message">
		<dt class="error">Error</dt>
		<dd class="error message fade">
			<ul><?php echo $this->error;?> </ul>
		</dd>
	</dl>
<?php endif; ?>
