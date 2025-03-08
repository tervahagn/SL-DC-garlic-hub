import './CanvasView.js';

export class ContextMenu
{
	MyCanvasView = {};
	options;
	context_menu;

	constructor(MyCanvasView)
	{
		this.MyCanvasView = MyCanvasView;
	}

	show(options)
	{
		if (options.target.getType() !== "LabeledZone" )
			return;

		this.build(options)
	}

	build(options)
	{
		this.options = options;

		this.context_menu = document.createElement("div");
		this.context_menu.style.position = "absolute";
		this.context_menu.style.zIndex = 1000;
		this.context_menu.style.left = this.options.e.pageX + "px";
		this.context_menu.style.top = this.options.e.pageY + "px";
		this.context_menu.innerHTML = document.getElementById("context-menu").innerHTML;
		document.body.append(this.context_menu);
	}

	remove()
	{
		if (this.context_menu !== undefined)
			this.context_menu.remove();
	}
}