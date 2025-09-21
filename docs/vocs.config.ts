import { defineConfig } from "vocs";

export default defineConfig({
	title: "Typographos",
	logoUrl:
		"data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏛️</text></svg>",
	iconUrl:
		"data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏛️</text></svg>",
	description: "Generate TS types from your PHP classes",
	rootDir: ".",
	sidebar: {
		"/": [
			{
				text: "Getting Started",
				items: [
					{
						text: "Introduction",
						link: "/introduction",
					},
					{
						text: "Installation",
						link: "/installation",
					},
					{
						text: "Quick Start",
						link: "/quick-start",
					},
				],
			},
			{
				text: "Guide",
				items: [
					{
						text: "Basic Usage",
						link: "/guide/basic-usage",
					},
					{
						text: "Configuration",
						link: "/guide/configuration",
					},
					{
						text: "Arrays",
						link: "/guide/arrays",
					},
					{
						text: "Enums",
						link: "/guide/enums",
					},
					{
						text: "Inline Types",
						link: "/guide/inline",
					},
					{
						text: "Type Replacements",
						link: "/guide/type-replacements",
					},
				],
			},
			{
				text: "API Reference",
				items: [
					{
						text: "Generator",
						link: "/api/generator",
					},
					{
						text: "Attributes",
						link: "/api/attributes",
					},
					{
						text: "Enums",
						link: "/api/enums",
					},
				],
			},
		],
	},
	socials: [
		{
			icon: "github",
			link: "https://github.com/LeTamanoir/Typographos",
		},
	],
	theme: {
		accentColor: { light: "#033eb7", dark: "#FFD63E" },
	},
	editLink: {
		pattern:
			"https://github.com/LeTamanoir/Typographos/edit/main/docs/pages/:path",
		text: "Edit on GitHub",
	},
});
