export class DragDropHandler
{
	#dropTarget  = null;
	#dropSource  = null;
	#dragItem    = null;
	#itemService = null;
	#itemList    = null;
	#mediaItems   = null;
	#drake        = null;
	#playlistId     = 0;

	constructor(dropTarget, itemService, itemList)
	{
		this.#dropTarget  = dropTarget;
		this.#itemService = itemService;
		this.#itemList    = itemList;
		this.preparePlaylistDragDrop();
	}


	set playlistId(value)
	{
		this.#playlistId = value;
	}

	addDropSource(value)
	{
		this.#dropSource = value;
		this.#drake.destroy();
		this.#drake = null;
		this.preparePlaylistDragDrop(true);
	}

	set mediaItems(value)
	{
		this.#mediaItems = value;
	}

	preparePlaylistDragDrop(hasDropSource = false)
	{
		const options = {
			copy: (el) =>{ // copy allowed only if source is another container
				return el.classList.contains('media-item') === true;
			},
			accepts: (el, target, source) => {
				if (source === this.#dropSource)
					return target === this.#dropTarget;

				return source === this.#dropTarget && target === this.#dropTarget;

			}
		};

		let dropContainers = [this.#dropTarget];
		if (hasDropSource === true)
			dropContainers.push(this.#dropSource);

		this.#drake   = dragula(dropContainers, options)
			.on('drag', (el, source) => {
				if (source === this.#dropSource)
					this.#dragItem = this.#mediaItems[el.getAttribute('data-media-id')];
			})
			.on('shadow', (el) => {
				if (el.classList.contains('media-item'))
					el.classList.add('dragula-shadow');
			})
			.on('drop', async (el, target, source, sibling) => {
				if (target === null)
					return; // prevent error when drop is canceled

				if (source === target)
				{
					const itemsPosition = {};
					Array.from(target.children).forEach((child, index) =>
					{
						itemsPosition[index + 1] = child.getAttribute('id').split('-')[1];
					});
					// for debug onlyconsole.log(itemsPosition);

					await this.#itemService.updateItemsOrders(this.#playlistId, itemsPosition);
					return;
				}

				let droppedIndex;
				if (sibling === null) // element dropped at end of list
				{
					droppedIndex = target.children.length;
				}
				else // Element dropped before 'sibling'
				{
					// We find the index of 'sibling' in the  'target'-Container
					droppedIndex = Array.from(target.children).indexOf(sibling);
				}
				const mediaId = el.getAttribute('data-media-id');
				let result = await this.#itemService.insertFromMediaPool(mediaId, this.#playlistId, droppedIndex);
				this.#itemList.createPlaylistItem(result.data.item, droppedIndex);
				this.#itemList.displayPlaylistProperties(result.data.playlist);

				// for debug only console.log('Element:','Position: ', droppedIndex);
				el.remove();
			});
	}
}