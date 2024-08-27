import { Component } from '@angular/core';

import templateString from './loader.component.html'
import cssString from './loader.component.scss'

@Component({
  selector: 'app-loader',
  template:  templateString || 'Template Not Loaded !!!',
  styles: [cssString || 'CSS Not Loaded !!!']
})
export class LoaderComponent
{
  constructor() { }
}
