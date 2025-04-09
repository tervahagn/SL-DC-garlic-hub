import {Item} from "./Item.js";

export class ItemFactory
{
	create(itemData)
	{
		return new Item(itemData);
	}
}