import {MediaService}      from "../../../mediapool/media/MediaService.js";
import {FetchClient}       from "../../../core/FetchClient.js";
import {SelectorView}      from "../../../mediapool/selector/SelectorView.js";
import {Selector}          from "../../../mediapool/selector/Selector.js";
import {WunderbaumWrapper} from "../../../mediapool/treeview/WunderbaumWrapper.js";
import {TreeViewElements}  from "../../../mediapool/treeview/TreeViewElements.js";
import {MediaFactory}      from "../../../mediapool/media/MediaFactory.js";
import {PlaylistsService}  from "./playlists/PlaylistsService.js";
import {PlaylistsSelector} from "./selectors/PlaylistsSelector.js";

export class SelectorFactory
{
	#mediaSelector = null;
	#playlistsSelector = null;

	create(type)
	{
		switch (type)
		{
			case 'mediaselector':
				if (this.#mediaSelector === null)
				{
					this.#mediaSelector = new Selector(
						new WunderbaumWrapper(new TreeViewElements()),
						new MediaService(new FetchClient()),
						new SelectorView(new MediaFactory(document.getElementById('mediaTemplate')))
					);
				}
				return this.#mediaSelector;
			case 'playlistselector':
				if (this.#playlistsSelector === null)
				{
					this.#playlistsSelector = new PlaylistsSelector(
						new PlaylistsService(new FetchClient())
					);
				}
				return this.#mediaSelector;
		}
	}
}