import { LabeledZone } from './LabeledZone.js';

export class LabeledZoneFactory
{
	constructor(defaultOptions = {})
	{
		this.defaultOptions = {
			left: 0,
			top: 0,
			width: 200,
			height: 100,
			fill: "#222222",
			fontSize: 20,
			...defaultOptions,  // Ermöglicht das Überschreiben von Standardwerten bei der Erstellung der Factory
		};
	}

	create(options = {})
	{
		const finalOptions = { ...this.defaultOptions, ...options };

		return new LabeledZone(finalOptions);
	}
}
