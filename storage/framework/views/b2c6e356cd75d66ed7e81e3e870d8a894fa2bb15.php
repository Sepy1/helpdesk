<?php $__env->startComponent('mail::message'); ?>
# Tiket Diterima

Halo <?php echo e($ticket->user->name ?? 'Pengguna'); ?>,

Tiket Anda telah berhasil diterima dengan rincian berikut:


- **No. Tiket:** <?php echo e($ticket->nomor_tiket ?? $ticket->id); ?>

- **Judul:** <?php echo e($ticket->kategori ?? optional($ticket->category)->name ?? '-'); ?>

- **Deskripsi:**
<?php echo nl2br(e($ticket->deskripsi ?? '-')); ?>



Kami akan memproses tiket ini sesegera mungkin.

Terima kasih,  
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/emails/tickets/submitted.blade.php ENDPATH**/ ?>