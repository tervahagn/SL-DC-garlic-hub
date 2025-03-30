export class EventEmitter
{
	#events = {};

	on(eventName, listener)
	{
		if (!this.#events[eventName])
			this.#events[eventName] = [];

		this.#events[eventName].push(listener);
		return this; // Für Method Chaining
	}

	off(eventName, listener)
	{
		if (!this.#events[eventName]) return this;

		if (listener)
			this.#events[eventName] = this.#events[eventName].filter(l => l !== listener);
		 else
			delete this.#events[eventName];

		return this;
	}

	emit(eventName, ...args)
	{
		if (!this.#events[eventName]) return false;

		this.#events[eventName].forEach(listener => {
			listener(...args);
		});
		return true;
	}

	once(eventName, listener)
	{
		const onceWrapper = (...args) => {
			this.off(eventName, onceWrapper);
			listener(...args);
		};
		return this.on(eventName, onceWrapper);
	}
}