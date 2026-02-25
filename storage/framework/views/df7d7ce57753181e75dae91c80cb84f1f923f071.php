<?php $__env->startComponent('mail::message'); ?>
# Tiket Ditutup

Halo <?php echo e($ticket->user->name ?? 'Pengguna'); ?>,

Tiket Anda dengan nomor **<?php echo e($ticket->nomor_tiket ?? $ticket->id); ?>** telah ditindak lanjuti dan ditutup oleh tim kami dengan keterangan sebagai berikut :

- **Status:** Selesai
- **Kategori:** <?php echo e($ticket->kategori ?? optional($ticket->category)->name ?? '-'); ?>

- **Tindak Lanjut:**
<?php echo nl2br(e($ticket->closed_note ?? '-')); ?>




Salam,  
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/emails/tickets/closed.blade.php ENDPATH**/ ?>