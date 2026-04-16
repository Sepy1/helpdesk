<?php $__env->startComponent('mail::message'); ?>

# <?php echo e($data['title'] ?? 'Notifikasi Tiket'); ?>


<?php if(!empty($data['actor_name'])): ?>
Dari: **<?php echo e($data['actor_name']); ?>**

<?php endif; ?>

<?php if(isset($data['kind']) && $data['kind'] === 'comment'): ?>
<?php echo e($data['body'] ?? ''); ?>


<?php else: ?>
<?php echo e($data['body'] ?? ''); ?>


<?php endif; ?>

<?php if(!empty($data['ticket_no'])): ?>
Nomor tiket: **<?php echo e($data['ticket_no']); ?>**
<?php endif; ?>

<?php $__env->startComponent('mail::button', ['url' => $data['url'] ?? url('/')]); ?>
Lihat Tiket
<?php echo $__env->renderComponent(); ?>

Terima kasih,<br>
<?php echo e(config('app.name')); ?>


<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/emails/notifications/ticket_activity.blade.php ENDPATH**/ ?>