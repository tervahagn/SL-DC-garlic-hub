// Lightweight playlist drag-and-drop helper
// Non-destructive: only updates a hidden input `playlist_order` inside the form when reorder happens

export function initPlaylistDnD(root = document) {
  const lists = Array.from(root.querySelectorAll('.playlist-editor .playlist-items'));
  lists.forEach(list => {
    list.querySelectorAll('.playlist-item').forEach(item => {
      item.setAttribute('draggable', 'true');
      item.classList.remove('dragging');
    });

    let dragging = null;

    list.addEventListener('dragstart', (e) => {
      const li = e.target.closest('.playlist-item');
      if (!li) return;
      dragging = li;
      li.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      try { e.dataTransfer.setData('text/plain', 'drag'); } catch (err) {}
    });

    list.addEventListener('dragend', (e) => {
      if (dragging) dragging.classList.remove('dragging');
      dragging = null;
    });

    list.addEventListener('dragover', (e) => {
      e.preventDefault();
      const after = getDragAfterElement(list, e.clientY);
      const draggingEl = list.querySelector('.playlist-item.dragging');
      if (!draggingEl) return;
      if (after == null) {
        list.appendChild(draggingEl);
      } else {
        list.insertBefore(draggingEl, after);
      }
    });

    list.addEventListener('drop', (e) => {
      e.preventDefault();
      updatePlaylistOrderInput(list);
    });

    // helper to get element after pointer
    function getDragAfterElement(container, y) {
      const draggableElements = [...container.querySelectorAll('.playlist-item:not(.dragging)')];
      return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
          return { offset: offset, element: child };
        } else {
          return closest;
        }
      }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function updatePlaylistOrderInput(container) {
      // find nearest form
      const form = container.closest('form') || document.querySelector('form');
      if (!form) return;
      let hidden = form.querySelector('input[name="playlist_order"]');
      if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'playlist_order';
        form.appendChild(hidden);
      }
      const order = [...container.querySelectorAll('.playlist-item')].map(el => el.dataset.id || el.getAttribute('data-id') || el.id || '').filter(Boolean);
      hidden.value = order.join(',');
      // dispatch change so server-side scripts can react if necessary
      hidden.dispatchEvent(new Event('change', { bubbles: true }));
    }

  });
}

// auto-init for DOMContentLoaded
if (typeof window !== 'undefined') {
  document.addEventListener('DOMContentLoaded', () => initPlaylistDnD(document));
}
