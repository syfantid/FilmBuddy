package content_analyzer;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;

/**
 * Class to process text (tweets and comments)
 * Created by Antigoni Founta on 13/4/2016.
 */

public class Processor {

    private static ArrayList<String> stopwordsList = new ArrayList<>();

    static {
        try {
            try(BufferedReader br = new BufferedReader(new FileReader("input\\stopwords.txt"))) {
                String line = br.readLine();

                while (line != null) {
                    stopwordsList.add(line);
                    line = br.readLine();
                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    /**
     * Processes a string
     * @param input The string before the processing
     * @return The parsed string
     */
    public static String preprocess(String input){
        String[] temp = tokenizer(input);
        if(temp.length < 10) { // There is not extended plot technically
            return "";
        }
        String output = "";
        for(String s : temp){
            s = finalizeText(s);
            if(isWhitelisted(s) && !s.isEmpty()){
                System.out.println(s);
                output = output + " ";
                output = output + s;
            }
        }
        return output;
    }


    /**
     * Method checking if a string is white-listed
     * A string is called white-listed if it's not a stop-word or a number or a URL
     * @param str The string to be checked
     * @return True if the string is white-listed, false otherwise
     */
    private static boolean isWhitelisted(String str){
        return !isStopWord(str) && !isNumeric(str) && !isURL(str);
    }

    /**
     * Checks whether a string contains URLs or not
     * @param str The string to be checked
     * @return True, if the string contains a URL, or false otherwise
     */
    private static boolean isURL(String str){
        try {
            URL url = new URL(str);
            return true;
        } catch (MalformedURLException e){
            return false;
        }
    }

    /**
     * Check whether a string contains numeric characters or not
     * @param str The string to be checked
     * @return True, if the string contains numeric characters, or false otherwise
     */
    private static boolean isNumeric(String str){
        return str.matches(".*\\d.*");
    }

    /**
     * Check whether a string contains stop-words or not
     * @param str The string to be checked
     * @return True, if the string contains stop-words, or false otherwise
     */
    private static boolean isStopWord(String str){
        return stopwordsList.contains(str);
    }

    /**
     * Separates string into words based on whitespaces
     * @param input The string to be tokenized
     * @return An array of the strings' words
     */
    private static String[] tokenizer(String input){
        return input.split(" ");
    }

    /**
     * Converts all characters to lower case
     * @param input The string to be turned into lower case
     * @return The string converted to lower case
     */
    private static String toLowerCase(String input){
        return input.toLowerCase();
    }

    /**
     * Removes all punctuation
     * @param input The string to be stripped from punctuation
     * @return The string without punctuation
     */
    private static String removePunctuation(String input){
        input = input.replaceAll("[.,:;()\\[\\]{}?_\\-!\'*\"@#$%^&+=|~`>’<]+", " ");
        input = input.replaceAll("\\s+"," ");
        input = input.trim();

        return input;
    }

    /**
     * Removes all single characters from a string
     * @param input The string to be edited
     * @return The string without punctuation
     */
    private static String removeSingleCharacter(String input){
        input = input.replaceAll("\\b[a-z]\\b", " ");
        input = input.replaceAll("\\s+"," ");
        input = input.trim();

        return input;
    }

    /**
     * Removes useless characters like punctuation and single characters and transforms to lowercase
     * @param input The string to be checked
     * @return The string finalized
     */
    private static String finalizeText(String input){
        input = toLowerCase(input);
        input = removePunctuation(input);
        input = removeSingleCharacter(input);

        return input;
    }

}
