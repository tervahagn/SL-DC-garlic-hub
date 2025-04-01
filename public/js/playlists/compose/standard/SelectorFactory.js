import {MediaService}      from "../../../mediapool/media/MediaService.js";
import {FetchClient}       from "../../../core/FetchClient.js";
import {SelectorView}      from "../../../mediapool/selector/SelectorView.js";
import {Selector}          from "../../../mediapool/selector/Selector.js";
import {WunderbaumWrapper} from "../../../mediapool/treeview/WunderbaumWrapper.js";
import {TreeViewElements}  from "../../../mediapool/treeview/TreeViewElements.js";
import {MediaFactory}      from "../../../mediapool/media/MediaFactory.js";

export class SelectorFactory
{

	create(type)
	{
		switch (type)
		{
			case 'mediaselector':
				return new Selector(
					new WunderbaumWrapper(new TreeViewElements()),
					new MediaService(new FetchClient()),
					new SelectorView(new MediaFactory(document.getElementById('mediaTemplate')))
				);
		}
	}
}