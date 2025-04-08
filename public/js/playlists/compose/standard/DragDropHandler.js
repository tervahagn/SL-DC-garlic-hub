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

	setDropSource(value)
	{
		this.#dropSource = value;
		if (!this.#drake.containers.includes(value))
			this.#drake.containers.push(value);
	}

	set mediaItems(value)
	{
		this.#mediaItems = value;
	}

	preparePlaylistDragDrop()
	{
		const options = {
			copy: (el) =>{ // copy only if source
				return el.classList.contains('media-item') === true;
			},
			accepts: (el, target, source) => {
				if (source === this.#dropSource)
					return target === this.#dropTarget;

				return source === this.#dropTarget && target === this.#dropTarget;

			}
		};

		this.#drake   = dragula([this.#dropTarget], options)
			.on('drop', async (el, target, source, sibling) => {
				if (source === target)
				{
					const itemsPosition = {};
					Array.from(target.children).forEach((child, index) =>
					{
						itemsPosition[index + 1] = child.getAttribute('id').split('-')[1];
					});
					console.log(itemsPosition);

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

				console.log('Element:','Position: ', droppedIndex);
				el.remove();

			});
		;
	}

	prepareDragDropOld()
	{
		const options = {
			copy: (el, target) =>{ // copy only if source
				return el.classList.contains('media-item') === true;
			},
			accepts: (el, target, source) => {
				if (source === this.#dropSource)
					return target === this.#dropTarget;

				return source === this.#dropTarget && target === this.#dropTarget;

			}
		};

		dragula([this.#dropSource, this.#dropTarget], options)
			.on('drag', (el, source) => {
				if (source === this.#dropSource)
					this.#dragItem = this.#mediaItems[el.getAttribute('data-media-id')];
			})
			.on('shadow', (el) => {
				if (el.classList.contains('media-item'))
					el.classList.add('dragula-shadow');
			})
			.on('over', (el, container, source) => {
				if (source === this.#dropSource)
					return;

				el.classList.add('dragula-shadow');
			})
			.on('drop', async (el, target, source, sibling) => {
				if (source === this.#dropSource && target !== this.#dropTarget )
					return;

				if (source === this.#dropTarget)
					target = source;

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

				let result = await this.#itemService.insertFromMediaPool(args.media.mediaId, this.#playlistId, args.position);
				this.#itemList.createPlaylistItem(result.data.item, args.position);
				this.#itemList.displayPlaylistProperties(result.data.playlist);

				el.remove();
			});

	}
}