import $ from 'jquery';

		$(document).ready(function() {
			// Système de commentaires en AJAX
			const $commentForm = $('#comment-form');
			const $commentsList = $('#comments-list');
			const $commentsCount = $('#comments-count');

			$commentForm.on('submit', function(e) {
				e.preventDefault();

				const $submitBtn = $commentForm.find('button[type="submit"]');
				const originalBtnText = $submitBtn.html();

				// Désactiver le bouton et afficher un indicateur de chargement
				$submitBtn.html('Envoi en cours...').prop('disabled', true);

				$.ajax({
					url: $commentForm.attr('action'),
					method: 'POST',
					data: $commentForm.serialize(),
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							// Ajouter le nouveau commentaire à la liste
							$commentsList.prepend(response.commentHtml);

							// Mettre à jour le compteur de commentaires
							$commentsCount.text(response.commentsCount);

							// Réinitialiser le formulaire
							$commentForm[0].reset();

							// Afficher un message de succès
							showAlert('success', 'Votre commentaire a été publié avec succès !');
						} else {
							showAlert('danger', response.error || 'Une erreur est survenue lors de l\'envoi du commentaire');
						}
					},
					error: function() {
						showAlert('danger', 'Une erreur est survenue lors de l\'envoi du commentaire.');
					},
					complete: function() {
						// Réactiver le bouton
						$submitBtn.html(originalBtnText).prop('disabled', false);
					}
				});
			});

			// Système de "j'aime" en AJAX
			const csrfToken = $('meta[name="csrf-token-like"]').attr('content');

  			$(document).on('click', '.like-btn', function () {
    		const $btn       = $(this);
    		const articleId  = $btn.data('id');

    		$.ajax({
      			url: `/article/${articleId}/like`,
      			method: 'POST',
      			headers: {
        		'X-Requested-With': 'XMLHttpRequest',
        		'X-CSRF-TOKEN': csrfToken
      			},
      			dataType: 'json',
      		success(response) {
        			if (!response.success) return;

        // 1.  Mettre à jour le compteur
        		$btn.find('.like-count').text(response.likesCount);

        // 2.  Mettre à jour l’icône & le style
        		const $icon = $btn.find('i');
        		if (response.liked) {
          		$icon.removeClass('bi-heart').addClass('bi-heart-fill text-danger');
          		$btn.removeClass('btn-outline-danger').addClass('btn-danger');
        		} else {
          		$icon.removeClass('bi-heart-fill text-danger').addClass('bi-heart');
          		$btn.removeClass('btn-danger').addClass('btn-outline-danger');
        		}
      		},
      error() {
        // Affiche un toast ou une alerte Bootstrap si tu as showAlert()
        showAlert?.('danger', 'Impossible de mettre à jour le like.');
      }
    });
  });

			// Fonction pour afficher des alertes
			function showAlert(type, message) {
				const $alert = $(`
					${message}

				`);

				$('#alerts-container').append($alert);

				// Faire disparaître l'alerte après 5 secondes
				setTimeout(() => {
					$alert.alert('close');
				}, 5000);
			}
		});
